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
use function session_start, session_status, session_write_close;
use const PHP_SESSION_ACTIVE, PHP_SESSION_DISABLED;

/**
 * Implements a session storage adapter.
 *
 * Note: the session storage is only half persistent, as tokens are stored for the duration of the session.
 */
class SessionStorage extends OAuthStorageAbstract{

	/**
	 * the key name for the storage array in $_SESSION
	 */
	protected string $storageVar;

	/**
	 * SessionStorage (pseudo-) constructor.
	 */
	public function construct():void{
		$this->storageVar = $this->options->sessionStorageVar;

		// Determine if the session has started.
		$status = session_status();

		if($this->options->sessionStart && $status !== PHP_SESSION_DISABLED && $status !== PHP_SESSION_ACTIVE){
			session_start();
		}

		if(!isset($_SESSION[$this->storageVar])){
			$_SESSION[$this->storageVar] = [
				$this::KEY_TOKEN    => [],
				$this::KEY_STATE    => [],
				$this::KEY_VERIFIER => [],
			];
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


	/*
	 * Access token
	 */

	/**
	 * @inheritDoc
	 */
	public function storeAccessToken(AccessToken $token, string $provider):static{
		$_SESSION[$this->storageVar][$this::KEY_TOKEN][$this->getProviderName($provider)] = $this->toStorage($token);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $provider):AccessToken{

		if($this->hasAccessToken($provider)){
			return $this->fromStorage($_SESSION[$this->storageVar][$this::KEY_TOKEN][$this->getProviderName($provider)]);
		}

		throw new TokenNotFoundException;
	}

	/**
	 * @inheritDoc
	 */
	public function hasAccessToken(string $provider):bool{
		return !empty($_SESSION[$this->storageVar][$this::KEY_TOKEN][$this->getProviderName($provider)]);
	}

	/**
	 * @inheritDoc
	 */
	public function clearAccessToken(string $provider):static{
		unset($_SESSION[$this->storageVar][$this::KEY_TOKEN][$this->getProviderName($provider)]);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllAccessTokens():static{
		$_SESSION[$this->storageVar][$this::KEY_TOKEN] = [];

		return $this;
	}


	/*
	 * CSRF state
	 */

	/**
	 * @inheritDoc
	 */
	public function storeCSRFState(string $state, string $provider):static{

		if($this->options->useStorageEncryption === true){
			$state = $this->encrypt($state);
		}

		$_SESSION[$this->storageVar][$this::KEY_STATE][$this->getProviderName($provider)] = $state;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getCSRFState(string $provider):string{

		if(!$this->hasCSRFState($provider)){
			throw new StateNotFoundException;
		}

		$state = $_SESSION[$this->storageVar][$this::KEY_STATE][$this->getProviderName($provider)];

		if($this->options->useStorageEncryption === true){
			return $this->decrypt($state);
		}

		return $state;
	}

	/**
	 * @inheritDoc
	 */
	public function hasCSRFState(string $provider):bool{
		return !empty($_SESSION[$this->storageVar][$this::KEY_STATE][$this->getProviderName($provider)]);
	}

	/**
	 * @inheritDoc
	 */
	public function clearCSRFState(string $provider):static{
		unset($_SESSION[$this->storageVar][$this::KEY_STATE][$this->getProviderName($provider)]);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllCSRFStates():static{
		$_SESSION[$this->storageVar][$this::KEY_STATE] = [];

		return $this;
	}


	/*
	 * PKCE verifier
	 */

	/**
	 * @inheritDoc
	 */
	public function storeCodeVerifier(string $verifier, string $provider):static{

		if($this->options->useStorageEncryption === true){
			$verifier = $this->encrypt($verifier);
		}

		$_SESSION[$this->storageVar][$this::KEY_VERIFIER][$this->getProviderName($provider)] = $verifier;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getCodeVerifier(string $provider):string{

		if(!$this->hasCodeVerifier($provider)){
			throw new VerifierNotFoundException;
		}

		$verifier = $_SESSION[$this->storageVar][$this::KEY_VERIFIER][$this->getProviderName($provider)];

		if($this->options->useStorageEncryption === true){
			return $this->decrypt($verifier);
		}

		return $verifier;
	}

	/**
	 * @inheritDoc
	 */
	public function hasCodeVerifier(string $provider):bool{
		return !empty($_SESSION[$this->storageVar][$this::KEY_VERIFIER][$this->getProviderName($provider)]);
	}

	/**
	 * @inheritDoc
	 */
	public function clearCodeVerifier(string $provider):static{
		unset($_SESSION[$this->storageVar][$this::KEY_VERIFIER][$this->getProviderName($provider)]);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllCodeVerifiers():static{
		$_SESSION[$this->storageVar][$this::KEY_VERIFIER] = [];

		return $this;
	}

}
