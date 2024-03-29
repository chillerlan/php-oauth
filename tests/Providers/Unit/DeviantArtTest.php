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

/**
 * @property \chillerlan\OAuth\Providers\DeviantArt $provider
 */
final class DeviantArtTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return DeviantArt::class;
	}

	public function testTokenInvalidate():void{

		if(!$this->provider instanceof TokenInvalidate){
			$this::markTestSkipped('TokenInvalidate N/A');
		}

		$token = new AccessToken(['expires' => 42]);

		// DeviantArt responds with a "success" in the JSON body
		$this->setMockResponse($this->streamFactory->createStream('{"success":true}'));

		$this->provider->storeAccessToken($token);

		$this::assertTrue($this->storage->hasAccessToken($this->provider->name));
		$this::assertTrue($this->provider->invalidateAccessToken());
		$this::assertFalse($this->storage->hasAccessToken($this->provider->name));

		// token given via param
		$this::assertTrue($this->provider->invalidateAccessToken($token));
		$this::assertFalse($this->storage->hasAccessToken($this->provider->name));
	}

}
