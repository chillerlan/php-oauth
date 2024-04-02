<?php
/**
 * Trait OAuthOptionsTrait
 *
 * @created      29.01.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth;

use chillerlan\OAuth\Storage\OAuthStorageException;
use function is_dir, is_writable, realpath, sprintf, strlen, trim;
use const SODIUM_CRYPTO_SECRETBOX_KEYBYTES;

/**
 * The settings for the OAuth provider
 */
trait OAuthOptionsTrait{

	/**
	 * The application key (or client-id) given by your provider
	 */
	protected string $key = '';

	/**
	 * The application secret given by your provider
	 */
	protected string $secret = '';

	/**
	 * The (main) callback URL associated with your application
	 */
	protected string $callbackURL = '';

	/**
	 * Whether to use encryption for the file storage
	 *
	 * @see \chillerlan\OAuth\Storage\FileStorage
	 */
	protected bool $useStorageEncryption = false;

	/**
	 * The encryption key to use
	 *
	 * @see \sodium_crypto_secretbox_keygen()
	 * @see \chillerlan\OAuth\Storage\FileStorage
	 */
	protected string $storageEncryptionKey = '';

	/**
	 * Whether to automatically refresh access tokens (OAuth2)
	 *
	 * @see \chillerlan\OAuth\Core\TokenRefresh::refreshAccessToken()
	 */
	protected bool $tokenAutoRefresh = true;

	/**
	 * Whether to start the session when session storage is used
	 *
	 * Note: this will only start a session if there is no active session present
	 *
	 * @see \session_status()
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 */
	protected bool $sessionStart = true;

	/**
	 * Whether to end the session when session storage is used
	 *
	 * Note: this is set to `false` by default to not interfere with other session managers
	 *
	 * @see \session_status()
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 */
	protected bool $sessionStop = false;

	/**
	 * The session array key for token storage
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 */
	protected string $sessionTokenVar = 'chillerlan-oauth-token';

	/**
	 * The session array key for <state> storage (OAuth2)
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 */
	protected string $sessionStateVar = 'chillerlan-oauth-state';

	/**
	 * The file storage root path
	 *
	 * @see \chillerlan\OAuth\Storage\FileStorage
	 */
	protected string $fileStoragePath = '';

	/**
	 * sets an encryption key
	 */
	protected function set_storageEncryptionKey(string $storageEncryptionKey):void{

		if(strlen($storageEncryptionKey) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES){
			throw new OAuthStorageException('invalid encryption key');
		}

		$this->storageEncryptionKey = $storageEncryptionKey;
	}

	/**
	 * sets and verifies the file storage path
	 */
	protected function set_fileStoragePath(string $fileStoragePath):void{
		$path = realpath(trim($fileStoragePath));

		if($path === false || !is_dir($path) || !is_writable($path)){
			throw new OAuthStorageException(sprintf('invalid storage path "%s"', $fileStoragePath));
		}

		$this->fileStoragePath = $path;
	}

}
