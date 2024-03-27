<?php
/**
 * Class OAuthExampleSessionStorage
 *
 * @created      26.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\{OAuthStorageException, SessionStorage};
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class OAuthExampleSessionStorage extends SessionStorage{

	protected string|null $storagepath;

	/**
	 * OAuthExampleSessionStorage constructor.
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function __construct(
		OAuthOptions|SettingsContainerInterface $options = new OAuthOptions,
		LoggerInterface                         $logger = new NullLogger,
		string|null                             $storagepath = null,
	){
		parent::__construct($options, $logger);

		if($storagepath !== null){
			$storagepath = trim($storagepath);

			if(!is_dir($storagepath) || !is_writable($storagepath)){
				throw new OAuthStorageException('invalid storage path');
			}

			$storagepath = realpath($storagepath);
		}

		$this->storagepath = $storagepath;
	}

	/**
	 * @inheritDoc
	 */
	public function storeAccessToken(AccessToken $token, string $provider):static{
		parent::storeAccessToken($token, $provider);

		if($this->storagepath !== null){
			$tokenfile = sprintf('%s/%s.token.json', $this->storagepath, $this->getProviderName($provider));

			if(file_put_contents($tokenfile, $token->toJSON()) === false){
				throw new OAuthStorageException('unable to access file storage');
			}
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $provider):AccessToken{
		$provider = $this->getProviderName($provider);

		if($this->hasAccessToken($provider)){
			return (new AccessToken)->fromJSON($_SESSION[$this->tokenVar][$provider]);
		}

		if($this->storagepath !== null){
			$tokenfile = sprintf('%s/%s.token.json', $this->storagepath, $provider);

			if(file_exists($tokenfile)){
				return (new AccessToken)->fromJSON(file_get_contents($tokenfile));
			}
		}

		throw new OAuthStorageException(sprintf('token for provider "%s" not found', $provider));
	}

}
