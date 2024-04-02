<?php
/**
 * Class VimeoTest
 *
 * @created      09.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\{AccessToken, TokenInvalidate};
use chillerlan\OAuth\Providers\Vimeo;

/**
 * @property \chillerlan\OAuth\Providers\Vimeo $provider
 */
final class VimeoTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Vimeo::class;
	}

	public function testTokenInvalidate():void{

		if(!$this->provider instanceof TokenInvalidate){
			$this::markTestSkipped('TokenInvalidate N/A');
		}

		$token = new AccessToken(['expires' => 42]);

		// Vimeo responds with a 204
		$this->setMockResponse($this->responseFactory->createResponse(204));

		$this->provider->storeAccessToken($token);

		$this::assertTrue($this->storage->hasAccessToken($this->provider->name));
		$this::assertTrue($this->provider->invalidateAccessToken());
		$this::assertFalse($this->storage->hasAccessToken($this->provider->name));

		// token via param
		$this::assertTrue($this->provider->invalidateAccessToken($token));
		$this::assertFalse($this->storage->hasAccessToken($this->provider->name));
	}

}