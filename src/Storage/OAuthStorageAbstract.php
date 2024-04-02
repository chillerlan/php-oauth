<?php
/**
 * Class OAuthStorageAbstract
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Log\{LoggerInterface, NullLogger};
use function random_bytes, sodium_base642bin, sodium_bin2base64, sodium_bin2hex, sodium_crypto_secretbox,
	sodium_crypto_secretbox_open, sodium_hex2bin, sodium_memzero, substr, trim;
use const SODIUM_BASE64_VARIANT_ORIGINAL, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

/**
 * Implements an abstract OAuth storage adapter
 */
abstract class OAuthStorageAbstract implements OAuthStorageInterface{

	final protected const ENCRYPT_FORMAT_BINARY = 0b00;
	final protected const ENCRYPT_FORMAT_BASE64 = 0b01;
	final protected const ENCRYPT_FORMAT_HEX    = 0b10;

	protected const ENCRYPT_FORMAT = self::ENCRYPT_FORMAT_HEX;

	/**
	 * OAuthStorageAbstract constructor.
	 */
	public function __construct(
		protected OAuthOptions|SettingsContainerInterface $options = new OAuthOptions,
		protected LoggerInterface                         $logger = new NullLogger
	){

		if($this->options->useStorageEncryption === true && empty($this->options->storageEncryptionKey)){
			throw new OAuthStorageException('no encryption key given');
		}

		$this->construct();
	}

	/**
	 * A replacement constructor that you can call in extended classes,
	 * so that you don't have to implement the monstrous original `__construct()`
	 */
	protected function construct():void{
		// noop
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function setLogger(LoggerInterface $logger):static{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * Gets the current provider name
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	protected function getProviderName(string $provider):string{
		$name = trim($provider);

		if(empty($name)){
			throw new OAuthStorageException('provider name must not be empty');
		}

		return $name;
	}

	/**
	 * @inheritDoc
	 */
	public function toStorage(AccessToken $token):mixed{
		$tokenJSON = $token->toJSON();

		if($this->options->useStorageEncryption === true){
			return $this->encrypt($tokenJSON, $this->options->storageEncryptionKey);
		}

		return $tokenJSON;
	}

	/**
	 * @inheritDoc
	 */
	public function fromStorage(mixed $data):AccessToken{

		if($this->options->useStorageEncryption === true){
			$data = $this->decrypt($data, $this->options->storageEncryptionKey);
		}

		return (new AccessToken)->fromJSON($data);
	}

	/**
	 * encrypts the given $data with $key
	 *
	 * @see \sodium_crypto_secretbox()
	 * @see \sodium_bin2base64()
	 * @see \sodium_bin2hex()
	 */
	protected function encrypt(string $data, string $key):string{
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$box   = sodium_crypto_secretbox($data, $nonce, $key);

		$out = match($this::ENCRYPT_FORMAT){
			$this::ENCRYPT_FORMAT_BINARY => $nonce.$box,
			$this::ENCRYPT_FORMAT_BASE64 => sodium_bin2base64($nonce.$box, SODIUM_BASE64_VARIANT_ORIGINAL),
			$this::ENCRYPT_FORMAT_HEX    => sodium_bin2hex($nonce.$box),
		};

		sodium_memzero($data);
		sodium_memzero($key);
		sodium_memzero($nonce);
		sodium_memzero($box);

		return $out;
	}

	/**
	 * decrypts the given $encrypted data with $key
	 *
	 * @see \sodium_crypto_secretbox_open()
	 * @see \sodium_base642bin()
	 * @see \sodium_hex2bin()
	 */
	protected function decrypt(string $encrypted, string $key):string{

		$bin = match($this::ENCRYPT_FORMAT){
			$this::ENCRYPT_FORMAT_BINARY => $encrypted,
			$this::ENCRYPT_FORMAT_BASE64 => sodium_base642bin($encrypted, SODIUM_BASE64_VARIANT_ORIGINAL),
			$this::ENCRYPT_FORMAT_HEX    => sodium_hex2bin($encrypted),
		};

		$nonce = substr($bin, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$box   = substr($bin, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$data  = sodium_crypto_secretbox_open($box, $nonce, $key);

		sodium_memzero($encrypted);
		sodium_memzero($key);
		sodium_memzero($bin);
		sodium_memzero($nonce);
		sodium_memzero($box);

		if($data === false){
			throw new OAuthStorageException('decryption failed'); // @codeCoverageIgnore
		}

		return $data;
	}

}
