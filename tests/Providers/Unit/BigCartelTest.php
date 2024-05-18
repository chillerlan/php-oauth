<?php
/**
 * Class BigCartelTest
 *
 * @created      10.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\{AccessToken, TokenInvalidate};
use chillerlan\OAuth\Providers\BigCartel;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\BigCartel $provider
 */
#[Provider(BigCartel::class)]
final class BigCartelTest extends OAuth2ProviderUnitTestAbstract{

	public function testTokenInvalidate():void{

		if(!$this->provider instanceof TokenInvalidate){
			$this::markTestSkipped('TokenInvalidate N/A');
		}

		// BigCartel expects the account id set in the token and responds with a 204
		$this->setMockResponse($this->responseFactory->createResponse(204));

		$this->provider->storeAccessToken(new AccessToken(['expires' => 42, 'extraParams' => ['account_id' => 69]]));

		$this::assertTrue($this->storage->hasAccessToken($this->provider->getName()));
		$this::assertTrue($this->provider->invalidateAccessToken());
		$this::assertFalse($this->storage->hasAccessToken($this->provider->getName()));
	}

}
