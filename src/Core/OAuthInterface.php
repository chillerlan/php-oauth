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
 */
interface OAuthInterface extends ClientInterface{

	/**
	 * A common user agent string that can be used in requests
	 *
	 * @var string
	 */
	public const USER_AGENT = 'chillerlanPhpOAuth/1.0.0 +https://github.com/chillerlan/php-oauth';

	/**
	 * An identifier for the provider, usually the class name in ALLCAPS (required)
	 *
	 * @var string
	 */
	public const IDENTIFIER = '';

	/**
	 * additional headers to use during authentication
	 *
	 * Note: must not contain: Accept-Encoding, Authorization, Content-Length, Content-Type
	 *
	 * @var array<string, string>
	 */
	public const HEADERS_AUTH = [];

	/**
	 * additional headers to use during API access
	 *
	 * Note: must not contain: Authorization
	 *
	 * @var array<string, string>
	 */
	public const HEADERS_API = [];

	/**
	 * Default scopes to apply if none were provided via the $scopes parameter
	 *
	 * @var string[]
	 */
	public const DEFAULT_SCOPES = [];

	/**
	 * The delimiter string for scopes
	 *
	 * @var string
	 */
	public const SCOPES_DELIMITER = ' ';

	/**
	 * Returns the name of the provider/class
	 */
	public function getName():string;

	/**
	 * Returns the link to the provider's API docs, or null if the value is not set
	 */
	public function getApiDocURL():string|null;

	/**
	 * Returns the link to the provider's credential registration/application page, or null if the value is not set
	 */
	public function getApplicationURL():string|null;

	/**
	 * Returns the link to the page where a user can revoke access tokens, or null if the value is not set
	 */
	public function getUserRevokeURL():string|null;

	/**
	 * Prepares the URL with optional $params which redirects to the provider's authorization prompt
	 * and returns a PSR-7 UriInterface with all necessary parameters set.
	 *
	 * If the provider supports RFC-9126 "Pushed Authorization Requests (PAR)", a request to the PAR endpoint
	 * shall be made within this method in order to send authorization data and obtain a temporary request URI.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5849#section-2.2
	 * @link https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
	 * @link https://datatracker.ietf.org/doc/html/rfc9126
	 * @see \chillerlan\OAuth\Core\PAR
	 *
	 * @param array<string, scalar>|null $params
	 * @param string[]|null $scopes
	 */
	public function getAuthorizationURL(array|null $params = null, array|null $scopes = null):UriInterface;

	/**
	 * Authorizes the $request with the credentials from the given $token
	 * and returns a PSR-7 RequestInterface with all necessary headers and/or parameters set
	 *
	 * @see \chillerlan\OAuth\Core\OAuthProvider::sendRequest()
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken|null $token = null):RequestInterface;

	/**
	 * Prepares an API request to $path with the given parameters, gets authorization, fires the request
	 * and returns a PSR-7 ResponseInterface with the corresponding API response
	 *
	 * @param array<string, scalar|bool|null>|null                        $params
	 * @param StreamInterface|array<string, scalar|bool|null>|string|null $body
	 * @param array<string, string>|null                                  $headers
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

}
