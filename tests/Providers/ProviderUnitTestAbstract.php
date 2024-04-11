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

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\OAuthProviderFactory;
use chillerlan\OAuth\Storage\MemoryStorage;
use chillerlan\OAuth\Storage\OAuthStorageInterface;
use chillerlan\PHPUnitHttp\HttpFactoryTrait;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Throwable;
use function constant;
use function defined;
use function ini_set;
use function realpath;
use function sprintf;

/**
 *
 */
abstract class ProviderUnitTestAbstract extends TestCase{
	use HttpFactoryTrait;

	protected OAuthProviderFactory  $providerFactory;
	protected LoggerInterface       $logger;
	protected OAuthOptions          $options;
	protected OAuthStorageInterface $storage;
	protected OAuthInterface        $provider;
	protected ReflectionClass       $reflection; // reflection of the test subject

	protected string $HTTP_CLIENT_FACTORY = ProviderUnitTestHttpClientFactory::class;

	protected bool   $ENV_IS_CI;

	protected const PROJECT_ROOT = __DIR__.'/../..';
	protected const CFGDIR       = self::PROJECT_ROOT.'/.config';
	protected const CACERT       = self::PROJECT_ROOT.'/tests/cacert.pem';

	protected function setUp():void{
		ini_set('date.timezone', 'UTC');

		// are we running on CI? (travis, github) -> see phpunit.xml
		$this->ENV_IS_CI = defined('TEST_IS_CI') && constant('TEST_IS_CI') === true;

		try{
			$this->initConfig();
			$this->initFactories(realpath($this::CACERT));

			$this->logger  = (new ProviderTestLoggerFactory)->getLogger($this->ENV_IS_CI); // PSR-3 logger
			$this->options = $this->initOptions();
			$this->storage = $this->initStorage($this->options);

			$this->providerFactory = new OAuthProviderFactory(
				$this->httpClient,
				$this->requestFactory,
				$this->streamFactory,
				$this->uriFactory,
				$this->logger,
			);

			$this->provider   = $this->providerFactory->getProvider($this->getProviderFQCN(), $this->options, $this->storage);
			$this->reflection = new ReflectionClass($this->provider);
		}
		catch(Throwable $e){
			$this->markTestSkipped(sprintf("unable to init provider test: %s\n\n%s", $e->getMessage(), $e->getTraceAsString()));
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
	 * init dependencies
	 */

	protected function initConfig():void{

		foreach(['TEST_ENVFILE'] as $constant){
			if(!defined($constant)){
				throw new InvalidArgumentException(sprintf('constant "%s" not set -> see phpunit.xml', $constant));
			}
		}

	}

	protected function initOptions():OAuthOptions{
		$options = new OAuthOptions;

		$options->key              = 'testclient';
		$options->secret           = 'testsecret';
		$options->callbackURL      = 'https://localhost/callback';
		$options->tokenAutoRefresh = true;

		return $options;
	}

	protected function initStorage(OAuthOptions $options):OAuthStorageInterface{
		return new MemoryStorage($options);
	}


	/*
	 * Reflection utilities
	 */

	final protected function setReflectionProperty(string $property, mixed $value):void{
		$this->reflection->getProperty($property)->setValue($this->provider, $value);
	}

	final protected function getReflectionProperty(string $property):mixed{
		return $this->reflection->getProperty($property)->getValue($this->provider);
	}

	final protected function invokeReflectionMethod(string $method, array $args = []):mixed{
		return $this->reflection->getMethod($method)->invokeArgs($this->provider, $args);
	}


	/*
	 * misc helpers
	 */

	/**
	 * creates a stupid simple ClientInterface that returns the given response instance
	 */
	protected function getMockHttpClient(ResponseInterface $response):ClientInterface{
		return new class ($response) implements ClientInterface{

			public function __construct(
				private readonly ResponseInterface $mockedResponse
			){}

			public function sendRequest(RequestInterface $request):ResponseInterface{
				 return $this->mockedResponse;
			}

		};
	}

	/**
	 * sets a custom response in the mock http client and sets the client in the current provider
	 */
	protected function setMockResponse(ResponseInterface|StreamInterface $response):void{

		if($response instanceof StreamInterface){
			$response = $this->responseFactory
				->createResponse()
				->withBody($response)
			;
		}

		$this->setReflectionProperty('http', $this->getMockHttpClient($response));
	}

	/**
	 * Creates a test access token with the given parameters or a set of defaults
	 */
	protected function getTestToken(array|null $params = null):AccessToken{

		$params ??= [
			'accessToken'       => 'test_access_token',
			'accessTokenSecret' => 'test_access_token_secret',
			'refreshToken'      => 'test_refresh_token',
			'expires'           => 42,
			// patreon requires a scope set
			'scopes'            => ['identity', 'scope1', 'scope2'],
			'provider'          => $this->provider->name,
		];

		return new AccessToken($params);
	}

}
