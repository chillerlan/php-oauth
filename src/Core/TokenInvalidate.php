<?php
/**
 * Interface TokenInvalidate
 *
 * @created      12.02.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

/**
 * Indicates whether the service is capable of invalidating access tokens
 */
interface TokenInvalidate{

	/**
	 * Allows to invalidate an access token
	 *
	 * Clients shall set the optional OAuthProvider::$revokeURL for use in this method.
	 * If a token is given via $token, that token should be invalidated,
	 * otherwise the current user token from the internal storage should be used.
	 * Returns true if the operation was successful, false otherwise.
	 * May throw a ProviderException if an error occurred.
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function invalidateAccessToken(AccessToken|null $token = null):bool;

}
