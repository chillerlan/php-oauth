<?php
/**
 * Class ProviderLiveTestMemoryStorage
 *
 * @created      26.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types = 1);

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageException};
use chillerlan\Settings\SettingsContainerInterface;
use InvalidArgumentException;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_writable;
use function realpath;
use function sprintf;

/**
 * Extends the standard memory storage so that it also saves tokens as JSON in the given path
 */
final class ProviderLiveTestMemoryStorage extends MemoryStorage{

	protected string $storagePath;

	public function __construct(OAuthOptions|SettingsContainerInterface $options = null, string $storagePath = null){
		parent::__construct($options);

		$storagePath = realpath($storagePath ?? '');

		if($storagePath === false || !is_dir($storagePath) || !is_writable($storagePath)){
			throw new InvalidArgumentException('invalid storage path');
		}

		$this->storagePath = $storagePath;
	}

	/**
	 * @inheritDoc
	 */
	public function storeAccessToken(AccessToken $token, string $service = null):static{
		parent::storeAccessToken($token, $service);

		$tokenFile = sprintf('%s/%s.token.json', $this->storagePath, $this->getServiceName($service));

		if(file_put_contents($tokenFile, $token->toJSON()) === false){
			throw new OAuthStorageException('unable to access file storage');
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $service = null):AccessToken{
		$serviceName = $this->getServiceName($service);

		if($this->hasAccessToken($service)){
			return $this->tokens[$serviceName];
		}

		$tokenFile = sprintf('%s/%s.token.json', $this->storagePath, $serviceName);

		if(file_exists($tokenFile)){
			return (new AccessToken)->fromJSON(file_get_contents($tokenFile));
		}

		throw new OAuthStorageException('token not found');
	}

}
