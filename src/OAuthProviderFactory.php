<?php
/**
 * Class OAuthProviderFactory
 *
 * @created      25.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth;

use chillerlan\OAuth\Core\{OAuth1Interface, OAuth2Interface, OAuthInterface};
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageInterface};
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestFactoryInterface, StreamFactoryInterface, UriFactoryInterface};
use Psr\Log\{LoggerInterface, NullLogger};
use function class_exists;

/**
 * A simple OAuth provider factory (not sure if that clears the mess...)
 */
class OAuthProviderFactory{

	protected ClientInterface         $http;
	protected RequestFactoryInterface $requestFactory;
	protected StreamFactoryInterface  $streamFactory;
	protected UriFactoryInterface     $uriFactory;
	protected LoggerInterface         $logger;

	/**
	 * thank you PHP-FIG for absolutely nothing
	 */
	public function __construct(
		ClientInterface         $http,
		RequestFactoryInterface $requestFactory,
		StreamFactoryInterface  $streamFactory,
		UriFactoryInterface     $uriFactory,
		LoggerInterface         $logger = new NullLogger,
	){
		$this->http           = $http;
		$this->requestFactory = $requestFactory;
		$this->streamFactory  = $streamFactory;
		$this->uriFactory     = $uriFactory;
		$this->logger         = $logger;
	}

	/**
	 * invokes a provider instance with the given $options and $storage interfaces
	 */
	public function getProvider(
		string                                  $providerFQN,
		SettingsContainerInterface|OAuthOptions $options = new OAuthOptions,
		OAuthStorageInterface                   $storage = new MemoryStorage,
	):OAuthInterface|OAuth1Interface|OAuth2Interface{

		if(!class_exists($providerFQN)){
			throw new ProviderException('invalid provider class given');
		}

		return new $providerFQN(
			$options,
			$this->http,
			$this->requestFactory,
			$this->streamFactory,
			$this->uriFactory,
			$storage,
			$this->logger,
		);

	}

	/** @codeCoverageIgnore */
	public function setLogger(LoggerInterface $logger):static{
		$this->logger = $logger;

		return $this;
	}

	/*
	 * factory getters (convenience)
	 */

	/** @codeCoverageIgnore */
	public function getRequestFactory():RequestFactoryInterface{
		return $this->requestFactory;
	}

	/** @codeCoverageIgnore */
	public function getStreamFactory():StreamFactoryInterface{
		return $this->streamFactory;
	}

	/** @codeCoverageIgnore */
	public function getUriFactory():UriFactoryInterface{
		return $this->uriFactory;
	}

}
