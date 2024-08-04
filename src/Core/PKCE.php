<?php
/**
 * Interface PKCE
 *
 * @created      06.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

/**
 * Specifies the methods required for the OAuth2 Proof Key for Code Exchange (PKCE)
 *
 * @link https://datatracker.ietf.org/doc/html/rfc7636
 * @link https://github.com/AdrienGras/pkce-php
 */
interface PKCE{

	/** @var string */
	public const CHALLENGE_METHOD_PLAIN = 'plain';
	/** @var string */
	public const CHALLENGE_METHOD_S256  = 'S256';

	/** @var string */
	public const VERIFIER_CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~';

	/**
	 * generates a secure random "code_verifier"
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc7636#section-4.1
	 */
	public function generateVerifier(int $length):string;

	/**
	 * generates a "code_challenge" for the given $codeVerifier
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc7636#section-4.2
	 */
	public function generateChallenge(string $verifier, string $challengeMethod):string;

	/**
	 * Sets the PKCE code challenge parameters in a given array of query parameters and stores
	 * the verifier in the storage for later verification. Returns the updated array of parameters.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc7636#section-4.3
	 *
	 * @param array<string, string> $params
	 * @return array<string, string>
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function setCodeChallenge(array $params, string $challengeMethod):array;

	/**
	 * Sets the PKCE verifier parameter in a given array of query parameters
	 * and deletes it from the storage afterwards. Returns the updated array of parameters.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc7636#section-4.5
	 *
	 * @param array<string, string> $params
	 * @return array<string, string>
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function setCodeVerifier(array $params):array;

}
