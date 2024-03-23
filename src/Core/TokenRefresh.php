<?php
/**
 * Interface TokenRefresh
 *
 * @created      29.01.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

/**
 * Indicates whether the provider is capable of the OAuth2 token refresh.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-10.4
 */
interface TokenRefresh{

	/**
	 * Attempts to refresh an existing AccessToken with an associated refresh token and returns a fresh AccessToken.
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function refreshAccessToken(AccessToken|null $token = null):AccessToken;

}
