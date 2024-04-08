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
	 */
	protected array $storage = [
		self::KEY_TOKEN    => [],
		self::KEY_STATE    => [],
		self::KEY_VERIFIER => [],
	];

	/**
	 * @inheritDoc
	 */
	public function storeAccessToken(AccessToken $token, string $provider):static{
		$this->storage[$this::KEY_TOKEN][$this->getProviderName($provider)] = $token;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $provider):AccessToken{

		if($this->hasAccessToken($provider)){
			return $this->storage[$this::KEY_TOKEN][$this->getProviderName($provider)];
		}

		throw new TokenNotFoundException;
	}

	/**
	 * @inheritDoc
	 */
	public function hasAccessToken(string $provider):bool{
		return !empty($this->storage[$this::KEY_TOKEN][$this->getProviderName($provider)]);
	}

	/**
	 * @inheritDoc
	 */
	public function clearAccessToken(string $provider):static{
		unset($this->storage[$this::KEY_TOKEN][$this->getProviderName($provider)]);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllAccessTokens():static{
		$this->storage[$this::KEY_TOKEN] = [];

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function storeCSRFState(string $state, string $provider):static{
		$this->storage[$this::KEY_STATE][$this->getProviderName($provider)] = $state;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getCSRFState(string $provider):string{

		if($this->hasCSRFState($provider)){
			return $this->storage[$this::KEY_STATE][$this->getProviderName($provider)];
		}

		throw new StateNotFoundException;
	}

	/**
	 * @inheritDoc
	 */
	public function hasCSRFState(string $provider):bool{
		return !empty($this->storage[$this::KEY_STATE][$this->getProviderName($provider)]);
	}

	/**
	 * @inheritDoc
	 */
	public function clearCSRFState(string $provider):static{
		unset($this->storage[$this::KEY_STATE][$this->getProviderName($provider)]);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllCSRFStates():static{
		$this->storage[$this::KEY_STATE] = [];

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function storeCodeVerifier(string $verifier, string $provider):static{
		$this->storage[$this::KEY_VERIFIER][$this->getProviderName($provider)] = $verifier;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getCodeVerifier(string $provider):string{

		if($this->hasCodeVerifier($provider)){
			return $this->storage[$this::KEY_VERIFIER][$this->getProviderName($provider)];
		}

		throw new VerifierNotFoundException;
	}

	/**
	 * @inheritDoc
	 */
	public function hasCodeVerifier(string $provider):bool{
		return !empty($this->storage[$this::KEY_VERIFIER][$this->getProviderName($provider)]);
	}

	/**
	 * @inheritDoc
	 */
	public function clearCodeVerifier(string $provider):static{
		unset($this->storage[$this::KEY_VERIFIER][$this->getProviderName($provider)]);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllCodeVerifiers():static{
		$this->storage[$this::KEY_VERIFIER] = [];

		return $this;
	}

}
