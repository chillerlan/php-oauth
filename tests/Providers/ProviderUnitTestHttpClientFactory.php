<?php
/**
 * Class ProviderUnitTestHttpClientFactory
 *
 * @created      16.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers;

use chillerlan\HTTP\Utils\Client\EchoClient;
use chillerlan\PHPUnitHttp\HttpClientFactoryInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * A PSR-18 HTTP client factory for provider unit tests
 *
 * This factory just returns an echo client, that returns the given request
 *
 * @see  \chillerlan\PHPUnitHttp\HttpClientFactoryInterface
 * @link https://github.com/chillerlan/phpunit-http
 */
final class ProviderUnitTestHttpClientFactory implements HttpClientFactoryInterface{

	public function getClient(string $cacert, ResponseFactoryInterface $responseFactory):ClientInterface{
		return new EchoClient($responseFactory);
	}

}
