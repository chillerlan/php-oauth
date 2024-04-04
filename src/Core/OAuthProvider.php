<?php
/**
 * Class OAuthProvider
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageInterface};
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{
	RequestFactoryInterface, RequestInterface, ResponseInterface,
	StreamFactoryInterface, StreamInterface, UriFactoryInterface
};
use Psr\Log\{LoggerInterface, NullLogger};
use ReflectionClass, UnhandledMatchError;
use function array_merge, array_shift, explode, implode, in_array, is_array, is_string,
	json_encode, ltrim, random_bytes, rtrim, sodium_bin2hex, sprintf, str_contains,
	str_starts_with, strip_tags, strtolower;
use const PHP_QUERY_RFC1738;

/**
 * Implements an abstract OAuth provider with all methods required by the OAuthInterface.
 * It also implements a magic getter that allows to access the properties listed below.
 */
abstract class OAuthProvider implements OAuthInterface{

	/**
	 * the authentication URL
	 */
	protected string $authURL;

	/**
	 * an optional URL for application side token revocation
	 *
	 * @see \chillerlan\OAuth\Core\TokenInvalidate
	 */
	protected string $revokeURL;

	/**
	 * the provider's access token exchange URL
	 */
	protected string $accessTokenURL;

	/*
	 * magic properties (public readonly would be cool if the implementation wasn't fucking stupid)
	 */

	/** @var string[] */
	protected const MAGIC_PROPERTIES = [
		'apiDocs', 'apiURL', 'applicationURL', 'name', 'userRevokeURL',
	];

	/**
	 * the name of the provider/class (magic)
	 */
	protected string $name;

	/**
	 * the API base URL (magic)
	 */
	protected string $apiURL;

	/**
	 * an optional link to the provider's API docs (magic)
	 */
	protected string|null $apiDocs = null;

	/**
	 * an optional URL to the provider's credential registration/application page (magic)
	 */
	protected string|null $applicationURL = null;

	/**
	 * an optional link to the page where a user can revoke access tokens (magic)
	 */
	protected string|null $userRevokeURL = null;

	/**
	 * OAuthProvider constructor.
	 */
	final public function __construct(
		protected OAuthOptions|SettingsContainerInterface $options,
		protected ClientInterface                         $http,
		protected RequestFactoryInterface                 $requestFactory,
		protected StreamFactoryInterface                  $streamFactory,
		protected UriFactoryInterface                     $uriFactory,
		protected OAuthStorageInterface                   $storage = new MemoryStorage,
		protected LoggerInterface                         $logger = new NullLogger,
	){
		$this->name = (new ReflectionClass($this))->getShortName();

		$this->construct();
	}

	/**
	 * A replacement constructor that you can call in extended classes,
	 * so that you don't have to implement the monstrous original `__construct()`
	 */
	protected function construct():void{
		// noop
	}

	/**
	 * Magic getter for the properties specified in self::ALLOWED_PROPERTIES
	 */
	final public function __get(string $name):string|null{

		if(in_array($name, $this::MAGIC_PROPERTIES, true)){
			return $this->{$name};
		}

		return null;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	final public function setStorage(OAuthStorageInterface $storage):static{
		$this->storage = $storage;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	final public function getStorage():OAuthStorageInterface{
		return $this->storage;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	final public function setLogger(LoggerInterface $logger):static{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	final public function setRequestFactory(RequestFactoryInterface $requestFactory):static{
		$this->requestFactory = $requestFactory;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	final public function setStreamFactory(StreamFactoryInterface $streamFactory):static{
		$this->streamFactory = $streamFactory;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	final public function setUriFactory(UriFactoryInterface $uriFactory):static{
		$this->uriFactory = $uriFactory;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	final public function storeAccessToken(AccessToken $token):static{
		$this->storage->storeAccessToken($token, $this->name);

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	final public function getAccessTokenFromStorage():AccessToken{
		return $this->storage->getAccessToken($this->name);
	}

	/**
	 * Creates an access token with the provider set to $this->name
	 *
	 * @codeCoverageIgnore
	 */
	final protected function createAccessToken():AccessToken{
		return new AccessToken(['provider' => $this->name]);
	}

	/**
	 * Prepare request headers
	 */
	final protected function getRequestHeaders(array|null $headers = null):array{
		/** @noinspection PhpParamsInspection sup PHPStorm?? */
		return array_merge($this::HEADERS_API, ($headers ?? []));
	}

	/**
	 * Prepares the request URL
	 */
	final protected function getRequestURL(string $path, array|null $params = null):string{
		return QueryUtil::merge($this->getRequestTarget($path), $this->cleanQueryParams(($params ?? [])));
	}

	/**
	 * Cleans an array of query parameters
	 */
	protected function cleanQueryParams(iterable $params):array{
		return QueryUtil::cleanParams($params, QueryUtil::BOOLEANS_AS_INT_STRING, true);
	}

	/**
	 * Cleans an array of body parameters
	 */
	protected function cleanBodyParams(iterable $params):array{
		return QueryUtil::cleanParams($params, QueryUtil::BOOLEANS_AS_BOOL, true);
	}

	/**
	 * returns a 32 byte random string (in hexadecimal representation) for use as a nonce
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc5849#section-3.3
	 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-10.12
	 */
	protected function nonce(int $bytes = 32):string{
		return sodium_bin2hex(random_bytes($bytes));
	}

	/**
	 * @implements \chillerlan\OAuth\Core\TokenInvalidate
	 * @codeCoverageIgnore
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function InvalidateAccessToken(AccessToken|null $token = null):bool{
		throw new ProviderException('not implemented');
	}

	/**
	 * @inheritDoc
	 * @throws \chillerlan\OAuth\Core\InvalidAccessTokenException
	 */
	final public function sendRequest(RequestInterface $request):ResponseInterface{
		// get authorization only if we request the provider API,
		// shortcut reroute to the http client otherwise.
		// avoid sending bearer tokens to unknown hosts
		if(!str_starts_with((string)$request->getUri(), $this->apiURL)){
			return $this->http->sendRequest($request);
		}

		$request = $this->getRequestAuthorization($request);

		return $this->http->sendRequest($request);
	}

	/**
	 * @inheritDoc
	 * @throws \chillerlan\OAuth\Core\UnauthorizedAccessException
	 */
	public function request(
		string                            $path,
		array|null                        $params = null,
		string|null                       $method = null,
		StreamInterface|array|string|null $body = null,
		array|null                        $headers = null,
		string|null                       $protocolVersion = null,
	):ResponseInterface{
		$request = $this->requestFactory->createRequest(($method ?? 'GET'), $this->getRequestURL($path, $params));

		foreach($this->getRequestHeaders($headers) as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		if($body !== null){
			$request = $this->setRequestBody($body, $request);
		}

		if($protocolVersion !== null){
			$request = $request->withProtocolVersion($protocolVersion);
		}

		$response = $this->sendRequest($request);

		// we're gonna throw here immideately on unauthorized/forbidden
		if(in_array($response->getStatusCode(), [401, 403], true)){
			throw new UnauthorizedAccessException;
		}

		return $response;
	}

	/**
	 * Prepares the request body and sets it in the given RequestInterface, along with a Content-Length header
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	final protected function setRequestBody(StreamInterface|array|string $body, RequestInterface $request):RequestInterface{

		// convert the array to a string according to the Content-Type header
		if(is_array($body)){
			$body        = $this->cleanBodyParams($body);
			$contentType = strtolower($request->getHeaderLine('content-type'));

			try{
				$body = match($contentType){
					'application/x-www-form-urlencoded'            => QueryUtil::build($body, PHP_QUERY_RFC1738),
					'application/json', 'application/vnd.api+json' => json_encode($body),
				};
			}
			catch(UnhandledMatchError){
				throw new ProviderException('invalid content-type for the given array body');
			}

		}

		// we don't check if the given string matches the content type - this is the implementor's responsibility
		if(!$body instanceof StreamInterface){
			$body = $this->streamFactory->createStream($body);
		}

		return $request
			->withHeader('Content-length', (string)$body->getSize())
			->withBody($body)
		;
	}

	/**
	 * Determine the request target from the given URI (path segment or URL) with respect to $apiURL,
	 * anything except host and path will be ignored, scheme will always be set to "https".
	 * Throws if the host of a given URL does not match the host of $apiURL.
	 *
	 * @see \chillerlan\OAuth\Core\OAuthInterface::request()
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	protected function getRequestTarget(string $uri):string{
		$parsedURL  = $this->uriFactory->createUri($uri);
		$parsedHost = $parsedURL->getHost();
		$api        = $this->uriFactory->createUri($this->apiURL);

		if($parsedHost === ''){
			$parsedPath = $parsedURL->getPath();
			$apiURL     = rtrim((string)$api, '/');

			if($parsedPath === ''){
				return $apiURL;
			}

			return sprintf('%s/%s', $apiURL, ltrim($parsedPath, '/'));
		}

		// for some reason we were given a host name

		// we explicitly ignore any existing parameters here and enforce https
		$parsedURL = $parsedURL->withScheme('https')->withQuery('')->withFragment('');
		$apiHost   = $api->getHost();

		if($parsedHost === $apiHost){
			return (string)$parsedURL;
		}

		// ok, one last chance - we might have a subdomain in any of the hosts (messy)
		$strip_subdomains = function(string $host):string{
			$host = explode('.', $host);
			// don't come at me with .co.uk
			// phpcs:ignore
			while(count($host) > 2){
				array_shift($host);
			}

			return implode('.', $host);
		};

		if($strip_subdomains($parsedHost) === $strip_subdomains($apiHost)){
			return (string)$parsedURL;
		}

		throw new ProviderException(sprintf('given host (%s) does not match provider (%s)', $parsedHost , $apiHost));
	}

	/**
	 * prepares and sends the request to the provider's "me" endpoint and returns a ResponseInterface
	 */
	protected function sendMeRequest(string $endpoint, array|null $params = null):ResponseInterface{
		// we'll bypass the API check here as not all "me" endpoints align with the provider APIs
		$url     = $this->getRequestURL($endpoint, $params);
		$request = $this->requestFactory->createRequest('GET', $url);

		foreach($this->getRequestHeaders() as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		$request = $this->getRequestAuthorization($request);

		return $this->http->sendRequest($request);
	}

	/**
	 * fetches the provider's "me" endpoint and returns the JSON data as an array
	 *
	 * @see \chillerlan\OAuth\Core\UserInfo::me()
	 * @see \chillerlan\OAuth\Core\OAuthProvider::sendMeRequest()
	 * @see \chillerlan\OAuth\Core\OAuthProvider::handleMeResponseError()
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	final protected function getMeResponseData(string $endpoint, array|null $params = null):array{
		$response = $this->sendMeRequest($endpoint, $params);

		if($response->getStatusCode() === 200){
			$contentType = $response->getHeaderLine('Content-Type');

			// mixcloud sends a javascript content type for json...
			if(!str_contains($contentType, 'json') && !str_contains($contentType, 'javascript')){
				throw new ProviderException(sprintf('invalid content type "%s", expected JSON', $contentType));
			}

			return MessageUtil::decodeJSON($response, true);
		}

		// handle and throw the error
		$this->handleMeResponseError($response);
	}

	/**
	 * handles errors for the `me()` endpoints - one horrible block of code to catch them all
	 *
	 * we could simply throw a ProviderException and be done with it, but we're nice and try to provide a message too
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException|\chillerlan\OAuth\Core\UnauthorizedAccessException
	 */
	final protected function handleMeResponseError(ResponseInterface $response):void{
		$status = $response->getStatusCode();

		// in case these slipped through
		if(in_array($status, [400, 401, 403], true)){
			throw new UnauthorizedAccessException;
		}

		// the error response may be plain text or html in some cases
		if(!str_contains($response->getHeaderLine('Content-Type'), 'json')){
			$body = strip_tags(MessageUtil::getContents($response));

			throw new ProviderException(sprintf('user info error HTTP/%s, "%s"', $status, $body));
		}

		// json error, fine
		$json = MessageUtil::decodeJSON($response, true);

		// let's try the common fields
		foreach(['error_description', 'message', 'error', 'meta', 'data', 'detail', 'status', 'text'] as $err){

			if(isset($json[$err]) && is_string($json[$err])){
				throw new ProviderException($json[$err]);
			}
			elseif(is_array($json[$err])){
				foreach(['message', 'error', 'errorDetail', 'developer_message', 'msg', 'code'] as $errDetail){
					if(isset($json[$err][$errDetail]) && is_string($json[$err][$errDetail])){
						throw new ProviderException($json[$err][$errDetail]);
					}
				}
			}
		}

		// throw the status if we can't find a message
		throw new ProviderException(sprintf('user info error HTTP/%s', $status));
	}

}
