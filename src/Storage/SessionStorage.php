<?php
/**
 * Class SessionStorage
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Core\AccessToken;
use function array_key_exists, array_keys, session_start, session_status, session_write_close;
use const PHP_SESSION_ACTIVE, PHP_SESSION_DISABLED;

/**
 * Implements a session storage adapter.
 *
 * Note: the session storage is only half persistent, as tokens are stored for the duration of the session.
 */
class SessionStorage extends OAuthStorageAbstract{

	/**
	 * the key name for the token storage array in $_SESSION
	 */
	protected string $tokenVar;

	/**
	 * the key name for the CSRF token storage array in $_SESSION
	 */
	protected string $stateVar;

	/**
	 * SessionStorage (pseudo-) constructor.
	 */
	public function construct():void{
		$this->tokenVar = $this->options->sessionTokenVar;
		$this->stateVar = $this->options->sessionStateVar;

		// Determine if the session has started.
		$status = session_status();

		if($this->options->sessionStart && $status !== PHP_SESSION_DISABLED && $status !== PHP_SESSION_ACTIVE){
			session_start();
		}

		if(!isset($_SESSION[$this->tokenVar])){
			$_SESSION[$this->tokenVar] = [];
		}

		if(!isset($_SESSION[$this->stateVar])){
			$_SESSION[$this->stateVar] = [];
		}

	}

	/**
	 * SessionStorage destructor.
	 *
	 * @codeCoverageIgnore
	 */
	public function __destruct(){
		if($this->options->sessionStop && session_status() === PHP_SESSION_ACTIVE){
			session_write_close();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function storeAccessToken(AccessToken $token, string $provider):static{
		$_SESSION[$this->tokenVar][$this->getProviderName($provider)] = $this->toStorage($token);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $provider):AccessToken{

		if($this->hasAccessToken($provider)){
			return $this->fromStorage($_SESSION[$this->tokenVar][$this->getProviderName($provider)]);
		}

		throw new OAuthStorageException('token not found');
	}

	/**
	 * @inheritDoc
	 */
	public function hasAccessToken(string $provider):bool{
		return isset($_SESSION[$this->tokenVar], $_SESSION[$this->tokenVar][$this->getProviderName($provider)]);
	}

	/**
	 * @inheritDoc
	 */
	public function clearAccessToken(string $provider):static{
		$providerName = $this->getProviderName($provider);

		if(array_key_exists($providerName, $_SESSION[$this->tokenVar])){
			unset($_SESSION[$this->tokenVar][$providerName]);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllAccessTokens():static{

		foreach(array_keys($_SESSION[$this->tokenVar]) as $provider){
			unset($_SESSION[$this->tokenVar][$provider]);
		}

		unset($_SESSION[$this->tokenVar]);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function storeCSRFState(string $state, string $provider):static{
		$_SESSION[$this->stateVar][$this->getProviderName($provider)] = $state;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getCSRFState(string $provider):string{

		if($this->hasCSRFState($provider)){
			return $_SESSION[$this->stateVar][$this->getProviderName($provider)];
		}

		throw new OAuthStorageException('state not found');
	}

	/**
	 * @inheritDoc
	 */
	public function hasCSRFState(string $provider):bool{
		return isset($_SESSION[$this->stateVar], $_SESSION[$this->stateVar][$this->getProviderName($provider)]);
	}

	/**
	 * @inheritDoc
	 */
	public function clearCSRFState(string $provider):static{
		$providerName = $this->getProviderName($provider);

		if(array_key_exists($providerName, $_SESSION[$this->stateVar])){
			unset($_SESSION[$this->stateVar][$providerName]);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllCSRFStates():static{
		unset($_SESSION[$this->stateVar]);

		return $this;
	}

}
