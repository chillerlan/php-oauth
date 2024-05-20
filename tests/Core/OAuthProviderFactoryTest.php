<?php
/**
 * Class OAuthProviderFactoryTest
 *
 * @created      11.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @phan-file-suppress PhanUndeclaredProperty
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\OAuthProviderFactory;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\DummyOAuth1Provider;
use chillerlan\OAuthTest\Providers\ProviderUnitTestHttpClientFactory;
use chillerlan\PHPUnitHttp\HttpFactoryTrait;
use PHPUnit\Framework\TestCase;
use function realpath;

/**
 * Tests the OAuthProviderFactory class
 */
class OAuthProviderFactoryTest extends TestCase{
	use HttpFactoryTrait;

	protected const CACERT = __DIR__.'/../cacert.pem';

	protected string $HTTP_CLIENT_FACTORY = ProviderUnitTestHttpClientFactory::class;

	protected function setUp():void{
		$this->initFactories(realpath($this::CACERT));

		$this->providerFactory = new OAuthProviderFactory(
			$this->httpClient,
			$this->requestFactory,
			$this->streamFactory,
			$this->uriFactory,
		);

	}

	public function testGetProvider():void{
		$provider = $this->providerFactory->getProvider(DummyOAuth1Provider::class);

		$this::assertInstanceOf(OAuthInterface::class, $provider);
	}

	public function testGetProviderInvalidProviderClassException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid provider class given');

		$this->providerFactory->getProvider('\\some\\unknown\\class');
	}

}
