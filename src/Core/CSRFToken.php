<?php
/**
 * Interface CSRFToken
 *
 * @created      29.01.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

/**
 * Specifies the methods required for the OAuth2 CSRF token validation ("state parameter")
 *
 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-10.12
 */
interface CSRFToken{

	/**
	 * Checks whether the CSRF state was set and verifies against the last known state.
	 * Throws a ProviderException if the given state is empty, unknown or doesn't match the known state.
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 * @internal
	 */
	public function checkState(string|null $state = null):void;

	/**
	 * Sets the CSRF state parameter in a given array of query parameters and stores that value
	 * in the local storage for later verification. Returns the updated array of parameters.
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 * @internal
	 */
	public function setState(array $params):array;

}
