<?php
/**
 * Interface PAR
 *
 * @created      10.05.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use Psr\Http\Message\UriInterface;

/**
 * Specifies the methods required for the OAuth2 Pushed Authorization Requests (PAR)
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9126
 */
interface PAR{

	/**
	 * Sends the given authorization request parameters to the PAR endpoint and returns
	 * the full authorization URL including the URN obtained from the PAR request
	 *
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::$parAuthorizationURL
	 * *
	 * @link https://datatracker.ietf.org/doc/html/rfc9126#section-1.1
	 */
	public function getParRequestUri(array $body):UriInterface;

}
