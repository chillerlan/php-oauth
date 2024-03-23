<?php
/**
 * Class ProviderUnitTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\Core\OAuth1Interface;
use chillerlan\OAuth\Core\OAuth2Interface;
use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\MemoryStorage;
use chillerlan\OAuth\Storage\OAuthStorageInterface;
use chillerlan\PHPUnitHttp\HttpFactoryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Throwable;
use function constant;
use function defined;
use function ini_set;
use function realpath;

/**
 *
 */
abstract class ProviderUnitTestAbstract extends TestCase{
	use HttpFactoryTrait;

	protected LoggerInterface       $logger;
	protected OAuthOptions          $options;
	protected OAuthStorageInterface $storage;
	protected OAuthInterface        $provider;
	protected ReflectionClass       $reflection; // reflection of the test subject

	protected string $HTTP_CLIENT_FACTORY = ProviderUnitTestHttpClientFactory::class;

	protected bool $ENV_IS_CI;

	protected const PROJECT_ROOT = __DIR__.'/../../';
	protected const CACERT       = __DIR__.'/../cacert.pem';

	protected function setUp():void{
		ini_set('date.timezone', 'UTC');

		// are we running on CI? (travis, github) -> see phpunit.xml
		$this->ENV_IS_CI = defined('TEST_IS_CI') && constant('TEST_IS_CI') === true;

		try{
			$this->initFactories(realpath($this::CACERT));

			$this->logger   = (new ProviderTestLoggerFactory)->getLogger($this->ENV_IS_CI); // PSR-3 logger
			$this->options  = $this->initOptions();
			$this->storage  = $this->initStorage($this->options);
			$this->provider = $this->initProvider($this->getProviderFQCN());
		}
		catch(Throwable $e){
			$this->markTestSkipped('unable to init provider test: '.$e->getMessage().$e->getTraceAsString());
		}

	}


	/*
	 * abstract methods
	 */

	/**
	 * returns the fully qualified class name (FQCN) of the test subject
	 */
	abstract protected function getProviderFQCN():string;


	/*
	 * init provider & dependencies
	 */

	protected function initOptions():OAuthOptions{
		return new OAuthOptions([
			'key'              => 'testclient',
			'secret'           => 'testsecret',
			'callbackURL'      => 'https://localhost/callback',
			'tokenAutoRefresh' => true,
		]);
	}

	protected function initStorage(OAuthOptions $options):OAuthStorageInterface{
		return new MemoryStorage($options);
	}

	protected function initProvider(string $FQCN):OAuthInterface|OAuth1Interface|OAuth2Interface{

		$args = [
			$this->options,
			$this->httpClient,
			$this->requestFactory,
			$this->streamFactory,
			$this->uriFactory,
			$this->storage,
			$this->logger,
		];

		return $this->invokeReflection($FQCN, $args);
	}


	/*
	 * Reflection utilities
	 */

	final protected function invokeReflection(string $FQCN, array $args = []):object{
		$this->reflection = new ReflectionClass($FQCN);

		return $this->reflection->newInstanceArgs($args);
	}

	final protected function setReflectionProperty(string $property, mixed $value):void{
		$this->reflection->getProperty($property)->setValue($this->provider, $value);
	}

	final protected function getReflectionProperty(string $property):mixed{
		return $this->reflection->getProperty($property)->getValue($this->provider);
	}

	final protected function invokeReflectionMethod(string $method, array $args = []):mixed{
		return $this->reflection->getMethod($method)->invokeArgs($this->provider, $args);
	}

}
