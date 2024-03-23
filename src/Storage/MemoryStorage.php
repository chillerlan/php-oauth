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
	public function storeAccessToken(AccessToken $token, string $service):static{
		$this->tokens[$this->getServiceName($service)] = $token;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $service):AccessToken{

		if($this->hasAccessToken($service)){
			return $this->tokens[$this->getServiceName($service)];
		}

		throw new OAuthStorageException('token not found');
	}

	/**
	 * @inheritDoc
	 */
	public function hasAccessToken(string $service):bool{
		$serviceName = $this->getServiceName($service);

		return isset($this->tokens[$serviceName]) && $this->tokens[$serviceName] instanceof AccessToken;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAccessToken(string $service):static{
		$serviceName = $this->getServiceName($service);

		if(array_key_exists($serviceName, $this->tokens)){
			unset($this->tokens[$serviceName]);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllAccessTokens():static{

		foreach(array_keys($this->tokens) as $service){
			unset($this->tokens[$service]);
		}

		$this->tokens = [];

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function storeCSRFState(string $state, string $service):static{
		$this->states[$this->getServiceName($service)] = $state;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getCSRFState(string $service):string{

		if($this->hasCSRFState($service)){
			return $this->states[$this->getServiceName($service)];
		}

		throw new OAuthStorageException('state not found');
	}

	/**
	 * @inheritDoc
	 */
	public function hasCSRFState(string $service):bool{
		$serviceName = $this->getServiceName($service);

		return isset($this->states[$serviceName]) && null !== $this->states[$serviceName];
	}

	/**
	 * @inheritDoc
	 */
	public function clearCSRFState(string $service):static{
		$serviceName = $this->getServiceName($service);

		if(array_key_exists($serviceName, $this->states)){
			unset($this->states[$serviceName]);
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
