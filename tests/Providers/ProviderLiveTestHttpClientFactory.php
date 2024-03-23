<?php
/**
 * Class ProviderLiveTestHttpClientFactory
 *
 * @created      17.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\HTTP\Utils\Client\LoggingClient;
use chillerlan\PHPUnitHttp\HttpClientFactoryInterface;
use InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use function class_exists;
use function class_implements;
use function constant;
use function defined;
use function sprintf;

/**
 *
 */
class ProviderLiveTestHttpClientFactory implements HttpClientFactoryInterface{

	public function getClient(string $cacert, ResponseFactoryInterface $responseFactory):ClientInterface{

		if(!defined('HTTP_CLIENT_FACTORY')){
			throw new InvalidArgumentException('property/constant "HTTP_CLIENT_FACTORY" not defined -> see phpunit.xml');
		}

		$class = constant('HTTP_CLIENT_FACTORY');

		if(!class_exists($class) || class_implements(HttpClientFactoryInterface::class) === false){
			throw new InvalidArgumentException(sprintf('invalid class "%s"', $class));
		}

		/** @var \chillerlan\PHPUnitHttp\HttpClientFactoryInterface $httpClientFactory */
		$httpClientFactory = new $class;
		$loggerFactory     = new ProviderTestLoggerFactory;

		$client = $httpClientFactory->getClient($cacert, $responseFactory);
		$logger = $loggerFactory->getLogger(defined('TEST_IS_CI') && constant('TEST_IS_CI') === true);

		return new LoggingClient($client, $logger);
	}

}
