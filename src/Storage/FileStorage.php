<?php
/**
 * Class FileStorage
 *
 * @created      26.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\Settings\SettingsContainerInterface;
use chillerlan\Utilities\{Crypto, Directory, File};
use Psr\Log\{LoggerInterface, NullLogger};
use DirectoryIterator;
use Throwable;
use function dirname, implode, str_starts_with, substr, trim;
use const DIRECTORY_SEPARATOR;

/**
 * Implements a filesystem storage adapter.
 *
 * Please note that the storage root directory needs permissions 0777 or `is_writable()` will fail.
 * Subfolders created by this class will have permissions set to 0755.
 *
 * @see \is_writable()
 * @see \chillerlan\OAuth\OAuthOptions::$fileStoragePath
 */
class FileStorage extends OAuthStorageAbstract{

	final protected const ENCRYPT_FORMAT = Crypto::ENCRYPT_FORMAT_BINARY;

	/**
	 * A *unique* ID to identify the user within your application, e.g. database row id or UUID
	 */
	protected string|int $oauthUser;

	/**
	 * OAuthStorageAbstract constructor.
	 */
	public function __construct(
		string|int                              $oauthUser,
		OAuthOptions|SettingsContainerInterface $options = new OAuthOptions,
		LoggerInterface                         $logger = new NullLogger,
	){
		parent::__construct($options, $logger);

		$this->oauthUser = trim((string)$oauthUser);

		if($this->oauthUser === ''){
			throw new OAuthStorageException('invalid OAuth user');
		}

		if(empty($this->options->fileStoragePath)){
			throw new OAuthStorageException('no storage path given');
		}

	}


	/*
	 * Access token
	 */

	public function storeAccessToken(AccessToken $token, string $provider):static{
		$this->saveFile($this->toStorage($token), $this::KEY_TOKEN, $provider);

		return $this;
	}

	public function getAccessToken(string $provider):AccessToken{
		return $this->fromStorage($this->loadFile($this::KEY_TOKEN, $provider));
	}

	public function hasAccessToken(string $provider):bool{
		return File::exists($this->getFilepath($this::KEY_TOKEN, $provider));
	}

	public function clearAccessToken(string $provider):static{
		$this->deleteFile($this::KEY_TOKEN, $provider);

		return $this;
	}

	public function clearAllAccessTokens():static{
		$this->deleteAll($this::KEY_TOKEN);

		return $this;
	}


	/*
	 * CSRF state
	 */

	public function storeCSRFState(string $state, string $provider):static{

		if($this->options->useStorageEncryption === true){
			$state = $this->encrypt($state);
		}

		$this->saveFile($state, $this::KEY_STATE, $provider);

		return $this;
	}

	public function getCSRFState(string $provider):string{
		$state = $this->loadFile($this::KEY_STATE, $provider);

		if($this->options->useStorageEncryption === true){
			return $this->decrypt($state);
		}

		return $state;
	}

	public function hasCSRFState(string $provider):bool{
		return File::exists($this->getFilepath($this::KEY_STATE, $provider));
	}

	public function clearCSRFState(string $provider):static{
		$this->deleteFile($this::KEY_STATE, $provider);

		return $this;
	}

	public function clearAllCSRFStates():static{
		$this->deleteAll($this::KEY_STATE);

		return $this;
	}


	/*
	 * PKCE verifier
	 */

	public function storeCodeVerifier(string $verifier, string $provider):static{

		if($this->options->useStorageEncryption === true){
			$verifier = $this->encrypt($verifier);
		}

		$this->saveFile($verifier, $this::KEY_VERIFIER, $provider);

		return $this;
	}

	public function getCodeVerifier(string $provider):string{
		$verifier = $this->loadFile($this::KEY_VERIFIER, $provider);

		if($this->options->useStorageEncryption === true){
			return $this->decrypt($verifier);
		}

		return $verifier;
	}

	public function hasCodeVerifier(string $provider):bool{
		return File::exists($this->getFilepath($this::KEY_VERIFIER, $provider));
	}

	public function clearCodeVerifier(string $provider):static{
		$this->deleteFile($this::KEY_VERIFIER, $provider);

		return $this;
	}

	public function clearAllCodeVerifiers():static{
		$this->deleteAll($this::KEY_VERIFIER);

		return $this;
	}


	/*
	 * Common
	 */

	/**
	 * fetched the content from a file
	 */
	protected function loadFile(string $key, string $provider):string{
		$path = $this->getFilepath($key, $provider);

		try{
			return File::load($path);
		}
		catch(Throwable){
			throw new ItemNotFoundException($key);
		}

	}

	/**
	 * saves the given data to a file
	 */
	protected function saveFile(string $data, string $key, string $provider):void{
		$path = $this->getFilepath($key, $provider);
		$dir  = dirname($path);

		if(!Directory::exists($dir)){
			Directory::create($dir, 0o755, true); // @codeCoverageIgnore
		}

		File::save($path, $data);
	}

	/**
	 * deletes an existing file
	 */
	protected function deleteFile(string $key, string $provider):void{
		File::delete($this->getFilepath($key, $provider));
	}

	/**
	 * deletes all matching files
	 */
	protected function deleteAll(string $key):void{
		foreach(new DirectoryIterator($this->options->fileStoragePath) as $finfo){
			$name = $finfo->getFilename();

			if(!$finfo->isDir() || str_starts_with($name, '.')){
				continue;
			}

			$this->deleteFile($key, $name);
		}
	}

	/**
	 * gets the file path for $key (token/state), $provider and the given oauth user ID
	 */
	protected function getFilepath(string $key, string $provider):string{
		$provider = $this->getProviderName($provider);
		$hash     = Crypto::sha256($provider.$this->oauthUser.$key);
		$path     = [$this->options->fileStoragePath, $provider];

		for($i = 1; $i <= 2; $i++){ // @todo: subdir depth to options?
			$path[] = substr($hash, 0, $i);
		}

		$path[] = $hash;

		return implode(DIRECTORY_SEPARATOR, $path);
	}

}
