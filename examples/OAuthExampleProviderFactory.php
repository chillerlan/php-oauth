<?php
/**
 * Class OAuthExampleProviderFactory
 *
 * @created      03.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\DotEnv\DotEnv;
use chillerlan\OAuth\Core\OAuth1Interface;
use chillerlan\OAuth\Core\OAuth2Interface;
use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\OAuthProviderFactory;
use chillerlan\OAuth\Storage\FileStorage;
use chillerlan\OAuth\Storage\MemoryStorage;
use chillerlan\OAuth\Storage\OAuthStorageInterface;
use chillerlan\OAuth\Storage\SessionStorage;
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

	public const STORAGE_MEMORY  = 0b001;
	public const STORAGE_SESSION = 0b010;
	public const STORAGE_FILE    = 0b100;

	protected DotEnv $dotEnv;
	protected LoggerInterface $logger;
	protected OAuthOptions|SettingsContainerInterface $options;
	protected OAuthStorageInterface $fileStorage;

	public function __construct(
		protected OAuthProviderFactory $factory,
		protected string               $cfgDir = __DIR__.'/../.config',
		string                         $envFile = '.env',
		string                         $logLevel = LogLevel::INFO,
	){
		ini_set('date.timezone', 'UTC');

		$this->dotEnv      = (new DotEnv($this->cfgDir, $envFile, false))->load();
		$this->logger      = $this->initLogger($logLevel);
		$this->fileStorage = $this->initFileStorage();

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

	protected function initFileStorage():OAuthStorageInterface{
		$options = new OAuthOptions;

		$options->fileStoragePath = $this->cfgDir.'/.filestorage';

		return new FileStorage('oauth-example', $options, $this->logger);
	}

	public function getProvider(
		string $providerFQN,
		int    $storageType = self::STORAGE_SESSION,
	):OAuthInterface|OAuth1Interface|OAuth2Interface{
		$options = new OAuthOptions;
		/** @param \chillerlan\OAuth\Core\OAuthInterface $providerFQN */
		$options->key              = ($this->getEnvVar($providerFQN::IIDENTIFIER.'_KEY') ?? '');
		$options->secret           = ($this->getEnvVar($providerFQN::IDENTIFIER.'_SECRET') ?? '');
		$options->callbackURL      = ($this->getEnvVar($providerFQN::IDENTIFIER.'_CALLBACK_URL') ?? '');
		$options->tokenAutoRefresh = true;
		$options->sessionStart     = true;

		$storage = match($storageType){
			$this::STORAGE_MEMORY  => new MemoryStorage,
			$this::STORAGE_SESSION => new SessionStorage($options),
			$this::STORAGE_FILE    => $this->fileStorage,
		};

		return $this->factory->getProvider($providerFQN, $options, $storage);
	}

	public function getFileStorage():OAuthStorageInterface{
		return $this->fileStorage;
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
