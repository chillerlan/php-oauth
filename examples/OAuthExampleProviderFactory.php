<?php
/**
 * Class OAuthExampleProviderFactory
 *
 * @created      03.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

require_once __DIR__.'/OAuthExampleSessionStorage.php';

use chillerlan\DotEnv\DotEnv;
use chillerlan\OAuth\Core\OAuth1Interface;
use chillerlan\OAuth\Core\OAuth2Interface;
use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\MemoryStorage;
use chillerlan\Settings\SettingsContainerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 *
 */
class OAuthExampleProviderFactory{

	protected DotEnv $dotEnv;
	protected LoggerInterface $logger;
	protected OAuthOptions|SettingsContainerInterface $options;

	public function __construct(
		protected ClientInterface         $http,
		protected RequestFactoryInterface $requestFactory,
		protected StreamFactoryInterface  $streamFactory,
		protected UriFactoryInterface     $uriFactory,
		protected string                  $cfgDir = __DIR__.'/../.config',
		string                            $envFile = '.env',
		string|null                       $logLevel = null,
	){
		ini_set('date.timezone', 'UTC');

		$this->dotEnv = (new DotEnv($this->cfgDir, $envFile, false))->load();
		$this->logger = $this->initLogger($logLevel);
	}

	protected function initLogger(string|null $logLevel):LoggerInterface{
		$logger = new Logger('log', [new NullHandler]);

		if($logLevel !== null){
			$formatter = new LineFormatter(null, 'Y-m-d H:i:s', true, true);
			$formatter->setJsonPrettyPrint(true);

			$logHandler = (new StreamHandler('php://stdout', $logLevel))->setFormatter($formatter);

			$logger->pushHandler($logHandler);
		}

		return $logger;
	}

	public function getProvider(string $providerFQN, string $envVar, bool $sessionStorage = true):OAuthInterface|OAuth1Interface|OAuth2Interface{
		$options = new OAuthOptions;

		$options->key              = ($this->getEnvVar($envVar.'_KEY') ?? '');
		$options->secret           = ($this->getEnvVar($envVar.'_SECRET') ?? '');
		$options->callbackURL      = ($this->getEnvVar($envVar.'_CALLBACK_URL') ?? '');
		$options->tokenAutoRefresh = true;
		$options->sessionStart     = true;

		$storage = new MemoryStorage;

		if($sessionStorage === true){
			$storage = new OAuthExampleSessionStorage(options: $options, storagepath: $this->cfgDir);
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

	public function getEnvVar(string $var):mixed{
		return $this->dotEnv->get($var);
	}

	public function getLogger():LoggerInterface{
		return $this->logger;
	}

	public function getRequestFactory():RequestFactoryInterface{
		return $this->requestFactory;
	}

	public function getStreamFactory():StreamFactoryInterface{
		return $this->streamFactory;
	}

	public function getUriFactory():UriFactoryInterface{
		return $this->uriFactory;
	}

}
