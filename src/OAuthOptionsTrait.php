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
use chillerlan\Utilities\{Directory, File};
use function max, min, preg_match, sprintf, trim;

/**
 * The settings for the OAuth provider
 *
 * @property string $key
 * @property string $secret
 * @property string $callbackURL
 * @property bool   $useStorageEncryption
 * @property string $storageEncryptionKey
 * @property bool   $tokenAutoRefresh
 * @property bool   $sessionStart
 * @property bool   $sessionStop
 * @property string $sessionStorageVar
 * @property string $fileStoragePath
 * @property int    $pkceVerifierLength
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
	 * The encryption key (hexadecimal) to use
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
	 * The session key for the storage array
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 */
	protected string $sessionStorageVar = 'chillerlan-oauth-storage';

	/**
	 * The file storage root path (requires permissions 0777)
	 *
	 * @see \is_writable()
	 * @see \chillerlan\OAuth\Storage\FileStorage
	 */
	protected string $fileStoragePath = '';

	/**
	 * The length of the PKCE challenge verifier (43-128 characters)
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc7636#section-4.1
	 */
	protected int $pkceVerifierLength = 128;

	/**
	 * sets an encryption key
	 */
	protected function set_storageEncryptionKey(string $storageEncryptionKey):void{

		if(!preg_match('/^[a-f\d]{64}$/i', $storageEncryptionKey)){
			throw new OAuthStorageException('invalid encryption key');
		}

		$this->storageEncryptionKey = $storageEncryptionKey;
	}

	/**
	 * sets and verifies the file storage path
	 */
	protected function set_fileStoragePath(string $fileStoragePath):void{
		$path = File::realpath(trim($fileStoragePath));

		if(!Directory::isWritable($path) || !Directory::isReadable($path)){
			throw new OAuthStorageException(sprintf('invalid storage path "%s"', $fileStoragePath));
		}

		$this->fileStoragePath = $path;
	}

	/**
	 * clamps the PKCE verifier length between 43 and 128
	 */
	protected function set_pkceVerifierLength(int $pkceVerifierLength):void{
		$this->pkceVerifierLength = max(43, min(128, $pkceVerifierLength));
	}

}
