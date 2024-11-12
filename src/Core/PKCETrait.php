<?php
/**
 * Trait PKCETrait
 *
 * @created      19.09.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\Utilities\{Crypto, Str};
use const SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING;

/**
 * Implements PKCE (Proof Key for Code Exchange) functionality
 *
 * @see \chillerlan\OAuth\Core\PKCE
 */
trait PKCETrait{

	/**
	 * implements PKCE::setCodeChallenge()
	 *
	 * @see \chillerlan\OAuth\Core\PKCE::setCodeChallenge()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getAuthorizationURLRequestParams()
	 *
	 * @param array<string, string> $params
	 * @return array<string, string>
	 */
	final public function setCodeChallenge(array $params, string $challengeMethod):array{

		if(!isset($params['response_type']) || $params['response_type'] !== 'code'){
			throw new ProviderException('invalid authorization request params');
		}

		$verifier = $this->generateVerifier($this->options->pkceVerifierLength);

		$params['code_challenge']        = $this->generateChallenge($verifier, $challengeMethod);
		$params['code_challenge_method'] = $challengeMethod;

		$this->storage->storeCodeVerifier($verifier, $this->name);

		return $params;
	}

	/**
	 * implements PKCE::setCodeVerifier()
	 *
	 * @see \chillerlan\OAuth\Core\PKCE::setCodeVerifier()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getAccessTokenRequestBodyParams()
	 *
	 * @param array<string, string> $params
	 * @return array<string, string>
	 */
	final public function setCodeVerifier(array $params):array{

		if(!isset($params['grant_type'], $params['code']) || $params['grant_type'] !== 'authorization_code'){
			throw new ProviderException('invalid authorization request body');
		}

		$params['code_verifier'] = $this->storage->getCodeVerifier($this->name);

		// delete verifier after use
		$this->storage->clearCodeVerifier($this->name);

		return $params;
	}

	/**
	 * implements PKCE::generateVerifier()
	 *
	 * @see \chillerlan\OAuth\Core\PKCE::generateVerifier()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::setCodeChallenge()
	 */
	final public function generateVerifier(int $length):string{
		return Crypto::randomString($length, PKCE::VERIFIER_CHARSET);
	}

	/**
	 * implements PKCE::generateChallenge()
	 *
	 * @see \chillerlan\OAuth\Core\PKCE::generateChallenge()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::setCodeChallenge()
	 */
	final public function generateChallenge(string $verifier, string $challengeMethod):string{

		if($challengeMethod === PKCE::CHALLENGE_METHOD_PLAIN){
			return $verifier;
		}

		$verifier = match($challengeMethod){
			PKCE::CHALLENGE_METHOD_S256 => Crypto::sha256($verifier, true),
			// no other hash methods yet
			default                     => throw new ProviderException('invalid PKCE challenge method'), // @codeCoverageIgnore
		};

		return Str::base64encode($verifier, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
	}

}
