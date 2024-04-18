<?php
/**
 * Interface OAuth2Interface
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

/**
 * Specifies the basic methods for an OAuth2 provider.
 */
interface OAuth2Interface extends OAuthInterface{

	/** @var int */
	final public const AUTH_METHOD_HEADER = 1;
	/** @var int */
	final public const AUTH_METHOD_QUERY  = 2;

	/**
	 * Specifies the authentication method:
	 *
	 *   - OAuth2Interface::AUTH_METHOD_HEADER (Bearer, OAuth, ...)
	 *   - OAuth2Interface::AUTH_METHOD_QUERY (access_token, ...)
	 *
	 * @var int
	 */
	public const AUTH_METHOD = self::AUTH_METHOD_HEADER;

	/**
	 * The name of the authentication header in case of OAuth2Interface::AUTH_METHOD_HEADER
	 *
	 * @var string
	 */
	public const AUTH_PREFIX_HEADER = 'Bearer';

	/**
	 * The name of the authentication query parameter in case of OAuth2Interface::AUTH_METHOD_QUERY
	 *
	 * @var string
	 */
	public const AUTH_PREFIX_QUERY = 'access_token';

	/**
	 * This indicates that the current provider requires an `Authorization: Basic <base64(key:secret)>` header
	 * in the access token request, rather than the key and secret in the request body.
	 *
	 * It saves provider inplementations from the hassle to override the respective methods:
	 *
	 *   - `OAuth2Provider::getAccessTokenRequestBodyParams()`
	 *   - `OAuth2Provider::sendAccessTokenRequest()`
	 *
	 * I'm not sure where to put this: here or a feature interface (it's not exactly a feature).
	 * I'll leave it here for now, subject to change.
	 */
	public const USES_BASIC_AUTH_IN_ACCESS_TOKEN_REQUEST = false;

	/**
	 * Obtains an OAuth2 access token with the given $code, verifies the $state
	 * if the provider implements the CSRFToken interface, and returns an AccessToken object
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
	 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.3
	 */
	public function getAccessToken(string $code, string|null $state = null):AccessToken;

}
