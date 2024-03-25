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
use chillerlan\OAuth\OAuthProviderFactory;
use chillerlan\OAuth\Storage\MemoryStorage;
use chillerlan\Settings\SettingsContainerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 *
 */
class OAuthExampleProviderFactory{

	protected DotEnv $dotEnv;
	protected LoggerInterface $logger;
	protected OAuthOptions|SettingsContainerInterface $options;

	public function __construct(
		protected OAuthProviderFactory $factory,
		protected string               $cfgDir = __DIR__.'/../.config',
		string                         $envFile = '.env',
		string                         $logLevel = LogLevel::INFO,
	){
		ini_set('date.timezone', 'UTC');

		$this->dotEnv = (new DotEnv($this->cfgDir, $envFile, false))->load();
		$this->logger = $this->initLogger($logLevel);
		$this->factory->setLogger($this->logger);
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

	public function getProvider(
		string $providerFQN,
		string $envVar,
		bool   $sessionStorage = true,
	):OAuthInterface|OAuth1Interface|OAuth2Interface{
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

		return $this->factory->getProvider($providerFQN, $options, $storage);
	}

	public function getEnvVar(string $var):mixed{
		return $this->dotEnv->get($var);
	}

	public function getLogger():LoggerInterface{
		return $this->logger;
	}

	public function getRequestFactory():RequestFactoryInterface{
		return $this->factory->getRequestFactory();
	}

	public function getStreamFactory():StreamFactoryInterface{
		return $this->factory->getStreamFactory();
	}

	public function getUriFactory():UriFactoryInterface{
		return $this->factory->getUriFactory();
	}

}
