<?php
/**
 * Class DeviantArtTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\{AccessToken, TokenInvalidate};
use chillerlan\OAuth\Providers\DeviantArt;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\DeviantArt $provider
 */
#[Provider(DeviantArt::class)]
final class DeviantArtTest extends OAuth2ProviderUnitTestAbstract{

	public function testTokenInvalidate():void{

		if(!$this->provider instanceof TokenInvalidate){
			$this::markTestSkipped('TokenInvalidate N/A');
		}

		$token = new AccessToken(['expires' => 42]);

		// DeviantArt responds with a "success" in the JSON body
		$this->setMockResponse($this->streamFactory->createStream('{"success":true}'));

		$this->provider->storeAccessToken($token);

		$this::assertTrue($this->storage->hasAccessToken($this->provider->getName()));
		$this::assertTrue($this->provider->invalidateAccessToken());
		$this::assertFalse($this->storage->hasAccessToken($this->provider->getName()));

		// token via param

		// the current token shouldn't be deleted
		$token2 = clone $token;
		$token2->accessToken = 'still here';

		$this->provider->storeAccessToken($token2);

		$this::assertTrue($this->provider->invalidateAccessToken($token));
		$this::assertSame('still here', $this->provider->getStorage()->getAccessToken($this->provider->getName())->accessToken);
	}

}
