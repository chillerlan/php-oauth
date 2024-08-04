<?php
/**
 * Class MemoryStorage
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Core\AccessToken;

/**
 * Implements a memory storage adapter.
 *
 * Note: the memory storage is not persistent, as tokens are only stored during script runtime.
 */
class MemoryStorage extends OAuthStorageAbstract{

	/**
	 * the storage array
	 *
	 * @var array<int|string, array<int|string, mixed>> (the int keys are to keep phpstan silent)
	 */
	protected array $storage = [
		self::KEY_TOKEN    => [],
		self::KEY_STATE    => [],
		self::KEY_VERIFIER => [],
	];


	/*
	 * Access token
	 */

	public function storeAccessToken(AccessToken $token, string $provider):static{
		$this->storage[$this::KEY_TOKEN][$this->getProviderName($provider)] = $token;

		return $this;
	}

	public function getAccessToken(string $provider):AccessToken{

		if($this->hasAccessToken($provider)){
			return $this->storage[$this::KEY_TOKEN][$this->getProviderName($provider)];
		}

		throw new ItemNotFoundException($this::KEY_TOKEN);
	}

	public function hasAccessToken(string $provider):bool{
		return !empty($this->storage[$this::KEY_TOKEN][$this->getProviderName($provider)]);
	}

	public function clearAccessToken(string $provider):static{
		unset($this->storage[$this::KEY_TOKEN][$this->getProviderName($provider)]);

		return $this;
	}

	public function clearAllAccessTokens():static{
		$this->storage[$this::KEY_TOKEN] = [];

		return $this;
	}


	/*
	 * CSRF state
	 */

	public function storeCSRFState(string $state, string $provider):static{
		$this->storage[$this::KEY_STATE][$this->getProviderName($provider)] = $state;

		return $this;
	}

	public function getCSRFState(string $provider):string{

		if($this->hasCSRFState($provider)){
			return $this->storage[$this::KEY_STATE][$this->getProviderName($provider)];
		}

		throw new ItemNotFoundException($this::KEY_STATE);
	}

	public function hasCSRFState(string $provider):bool{
		return !empty($this->storage[$this::KEY_STATE][$this->getProviderName($provider)]);
	}

	public function clearCSRFState(string $provider):static{
		unset($this->storage[$this::KEY_STATE][$this->getProviderName($provider)]);

		return $this;
	}

	public function clearAllCSRFStates():static{
		$this->storage[$this::KEY_STATE] = [];

		return $this;
	}


	/*
	 * PKCE verifier
	 */

	public function storeCodeVerifier(string $verifier, string $provider):static{
		$this->storage[$this::KEY_VERIFIER][$this->getProviderName($provider)] = $verifier;

		return $this;
	}

	public function getCodeVerifier(string $provider):string{

		if($this->hasCodeVerifier($provider)){
			return $this->storage[$this::KEY_VERIFIER][$this->getProviderName($provider)];
		}

		throw new ItemNotFoundException($this::KEY_VERIFIER);
	}

	public function hasCodeVerifier(string $provider):bool{
		return !empty($this->storage[$this::KEY_VERIFIER][$this->getProviderName($provider)]);
	}

	public function clearCodeVerifier(string $provider):static{
		unset($this->storage[$this::KEY_VERIFIER][$this->getProviderName($provider)]);

		return $this;
	}

	public function clearAllCodeVerifiers():static{
		$this->storage[$this::KEY_VERIFIER] = [];

		return $this;
	}

}
