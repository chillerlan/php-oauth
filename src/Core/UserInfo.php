<?php
/**
 * Interface UserInfo
 *
 * @created      04.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

/**
 * This nterface indicates whether the service can provide information about the currently authenticated user,
 * usually via a "/me", "/user" or "/tokeninfo" endpoint.
 */
interface UserInfo{

	/**
	 * Returns information about the currently authenticated user (usually a /me or /user endpoint).
	 *
	 * @see \chillerlan\OAuth\Core\OAuthProvider::sendMeRequest()
	 * @see \chillerlan\OAuth\Core\OAuthProvider::getMeResponseData()
	 * @see \chillerlan\OAuth\Core\OAuthProvider::handleMeResponseError()
	 */
	public function me():AuthenticatedUser;

}
