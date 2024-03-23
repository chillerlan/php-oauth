<?php
/**
 * Interface OAuth1Interface
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

/**
 * Specifies the basic methods for an OAuth1 provider.
 */
interface OAuth1Interface extends OAuthInterface{

	/**
	 * Obtains an OAuth1 access token with the given $token and $verifier and returns an AccessToken object.
	 *
	 * The $token (request token) supplied via `$_GET['oauth_token']` should be in the storage at this point.
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc5849#section-2.3
	 */
	public function getAccessToken(string $requestToken, string $verifier):AccessToken;

}
