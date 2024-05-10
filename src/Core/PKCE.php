<?php
/**
 * Interface PKCE
 *
 * @created      06.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

/**
 * Specifies the methods required for the OAuth2 Proof Key for Code Exchange (PKCE)
 *
 * @see https://datatracker.ietf.org/doc/html/rfc7636
 * @see https://github.com/AdrienGras/pkce-php
 */
interface PKCE{

	public const CHALLENGE_METHOD_PLAIN = 'plain';
	public const CHALLENGE_METHOD_S256  = 'S256';

	public const VERIFIER_CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~';

	/**
	 * generates a secure random "code_verifier"
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc7636#section-4.1
	 */
	public function generateVerifier(int $length):string;

	/**
	 * generates a "code_challenge" for the given $codeVerifier
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc7636#section-4.2
	 */
	public function generateChallenge(string $verifier, string $challengeMethod):string;

	/**
	 * Sets the PKCE code challenge parameters in a given array of query parameters and stores
	 * the verifier in the storage for later verification. Returns the updated array of parameters.
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc7636#section-4.3
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function setCodeChallenge(array $params, string $challengeMethod):array;

	/**
	 * Sets the PKCE verifier parameter in a given array of query parameters
	 * and deletes it from the storage afterwards. Returns the updated array of parameters.
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc7636#section-4.5
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function setCodeVerifier(array $params):array;

}
