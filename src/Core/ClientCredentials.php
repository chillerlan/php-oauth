<?php
/**
 * Interface ClientCredentials
 *
 * @created      29.01.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

/**
 * Indicates whether the provider is capable of the OAuth2 client credentials authentication flow.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.4
 */
interface ClientCredentials{

	/**
	 * Obtains an OAuth2 client credentials token and returns an AccessToken
	 */
	public function getClientCredentialsToken(array|null $scopes = null):AccessToken;

}
