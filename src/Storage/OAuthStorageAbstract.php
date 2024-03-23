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
use function trim;

/**
 * Implements an abstract OAuth storage adapter
 */
abstract class OAuthStorageAbstract implements OAuthStorageInterface{

	/**
	 * OAuthStorageAbstract constructor.
	 */
	public function __construct(
		protected OAuthOptions|SettingsContainerInterface $options = new OAuthOptions,
		protected LoggerInterface                         $logger = new NullLogger
	){
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
	 * Gets the current service provider name
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	protected function getServiceName(string $service):string{
		$name = trim($service);

		if(empty($name)){
			throw new OAuthStorageException('service name must not be empty');
		}

		return $name;
	}

	/**
	 * @inheritDoc
	 */
	public function toStorage(AccessToken $token):mixed{
		return $token->toJSON();
	}

	/**
	 * @inheritDoc
	 */
	public function fromStorage(mixed $data):AccessToken{
		return (new AccessToken)->fromJSON($data);
	}

}
