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
	public const AUTH_METHOD_HEADER = 1;
	/** @var int */
	public const AUTH_METHOD_QUERY  = 2;

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
	public const SCOPE_DELIMITER = ' ';

	/**
	 * Obtains an OAuth2 access token with the given $code, verifies the $state
	 * if the provider implements the CSRFToken interface, and returns an AccessToken object
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
	 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.3
	 */
	public function getAccessToken(string $code, string|null $state = null):AccessToken;

}
