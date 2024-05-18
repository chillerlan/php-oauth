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
use chillerlan\OAuth\Core\{AccessToken, Utilities};
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Log\{LoggerInterface, NullLogger};
use function trim;

/**
 * Implements an abstract OAuth storage adapter
 */
abstract class OAuthStorageAbstract implements OAuthStorageInterface{

	final protected const KEY_TOKEN    = 'TOKEN';
	final protected const KEY_STATE    = 'STATE';
	final protected const KEY_VERIFIER = 'VERIFIER';

	protected const ENCRYPT_FORMAT = Utilities::ENCRYPT_FORMAT_HEX;

	/**
	 * OAuthStorageAbstract constructor.
	 */
	public function __construct(
		/** The options instance */
		protected OAuthOptions|SettingsContainerInterface $options = new OAuthOptions,
		/** A PSR-3 logger */
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
			return $this->encrypt($tokenJSON);
		}

		return $tokenJSON;
	}

	/**
	 * @inheritDoc
	 */
	public function fromStorage(mixed $data):AccessToken{

		if($this->options->useStorageEncryption === true){
			$data = $this->decrypt($data);
		}

		return (new AccessToken)->fromJSON($data);
	}

	/**
	 * encrypts the given $data
	 */
	protected function encrypt(string $data):string{
		return Utilities::encrypt($data, $this->options->storageEncryptionKey, $this::ENCRYPT_FORMAT);
	}

	/**
	 * decrypts the given $encrypted data
	 */
	protected function decrypt(string $encrypted):string{
		return Utilities::decrypt($encrypted, $this->options->storageEncryptionKey, $this::ENCRYPT_FORMAT);
	}

}
