<?php
/**
 * Interface OAuthInterface
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\OAuth\Storage\OAuthStorageInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\{
	RequestFactoryInterface, RequestInterface, ResponseInterface,
	StreamFactoryInterface, StreamInterface, UriFactoryInterface, UriInterface
};

/**
 * Specifies the basic methods for an OAuth provider.
 *
 * @property string      $serviceName
 * @property string      $apiURL
 * @property string|null $apiDocs
 * @property string|null $applicationURL
 * @property string|null $userRevokeURL
 */
interface OAuthInterface extends ClientInterface{

	/**
	 * additional headers to use during authentication
	 *
	 * Note: must not contain: Accept-Encoding, Authorization, Content-Length, Content-Type
	 *
	 * @var array
	 */
	public const HEADERS_AUTH = [];

	/**
	 * additional headers to use during API access
	 *
	 * Note: must not contain: Authorization
	 *
	 * @var array
	 */
	public const HEADERS_API = [];

	/**
	 * Prepares the URL with optional $params which redirects to the provider's authorization prompt
	 * and returns a PSR-7 UriInterface with all necessary parameters set
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc5849#section-2.2
	 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
	 */
	public function getAuthURL(array|null $params = null, array|null $scopes = null):UriInterface;

	/**
	 * Authorizes the $request with the credentials from the given $token
	 * and returns a PSR-7 RequestInterface with all necessary headers and/or parameters set
	 *
	 * @internal
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken|null $token = null):RequestInterface;

	/**
	 * Prepares an API request to $path with the given parameters, gets authorization, fires the request
	 * and returns a PSR-7 ResponseInterface with the corresponding API response
	 */
	public function request(
		string                            $path,
		array|null                        $params = null,
		string|null                       $method = null,
		StreamInterface|array|string|null $body = null,
		array|null                        $headers = null,
		string|null                       $protocolVersion = null,
	):ResponseInterface;

	/**
	 * Sets an optional OAuthStorageInterface
	 */
	public function setStorage(OAuthStorageInterface $storage):static;

	/**
	 * Returns the current OAuthStorageInterface
	 */
	public function getStorage():OAuthStorageInterface;

	/**
	 * Sets an access token in the current OAuthStorageInterface (shorthand/convenience)
	 */
	public function storeAccessToken(AccessToken $token):static;

	/**
	 * Gets an access token from the current OAuthStorageInterface (shorthand/convenience)
	 */
	public function getAccessTokenFromStorage():AccessToken;

	/**
	 * Sets an optional PSR-3 LoggerInterface
	 */
	public function setLogger(LoggerInterface $logger):static;

	/**
	 * Sets an optional PSR-17 RequestFactoryInterface
	 */
	public function setRequestFactory(RequestFactoryInterface $requestFactory):static;

	/**
	 * Sets an optional PSR-17 StreamFactoryInterface
	 */
	public function setStreamFactory(StreamFactoryInterface $streamFactory):static;

	/**
	 * Sets an optional PSR-17 UriFactoryInterface
	 */
	public function setUriFactory(UriFactoryInterface $uriFactory):static;

	/**
	 * Returns information about the currently authenticated user (usually a /me or /user endpoint).
	 * Throws a ProviderException if no such information is available or if the method cannot be implemented.
	 */
	public function me():ResponseInterface;

}
