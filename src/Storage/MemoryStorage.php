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
use function array_key_exists, array_keys;

/**
 * Implements a memory storage adapter.
 *
 * Note: the memory storage is not persistent, as tokens are only stored during script runtime.
 */
class MemoryStorage extends OAuthStorageAbstract{

	/**
	 * the token storage array
	 */
	protected array $tokens = [];

	/**
	 * the CSRF state storage array
	 */
	protected array $states = [];

	/**
	 * @inheritDoc
	 */
	public function storeAccessToken(AccessToken $token, string $provider):static{
		$this->tokens[$this->getProviderName($provider)] = $token;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $provider):AccessToken{

		if($this->hasAccessToken($provider)){
			return $this->tokens[$this->getProviderName($provider)];
		}

		throw new TokenNotFoundException;
	}

	/**
	 * @inheritDoc
	 */
	public function hasAccessToken(string $provider):bool{
		$providerName = $this->getProviderName($provider);

		return isset($this->tokens[$providerName]) && $this->tokens[$providerName] instanceof AccessToken;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAccessToken(string $provider):static{
		$providerName = $this->getProviderName($provider);

		if(array_key_exists($providerName, $this->tokens)){
			unset($this->tokens[$providerName]);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllAccessTokens():static{

		foreach(array_keys($this->tokens) as $provider){
			unset($this->tokens[$provider]);
		}

		$this->tokens = [];

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function storeCSRFState(string $state, string $provider):static{
		$this->states[$this->getProviderName($provider)] = $state;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getCSRFState(string $provider):string{

		if($this->hasCSRFState($provider)){
			return $this->states[$this->getProviderName($provider)];
		}

		throw new StateNotFoundException;
	}

	/**
	 * @inheritDoc
	 */
	public function hasCSRFState(string $provider):bool{
		$providerName = $this->getProviderName($provider);

		return isset($this->states[$providerName]) && null !== $this->states[$providerName];
	}

	/**
	 * @inheritDoc
	 */
	public function clearCSRFState(string $provider):static{
		$providerName = $this->getProviderName($provider);

		if(array_key_exists($providerName, $this->states)){
			unset($this->states[$providerName]);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllCSRFStates():static{
		$this->states = [];

		return $this;
	}

}
