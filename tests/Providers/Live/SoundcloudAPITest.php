<?php
/**
 * Class SoundcloudAPITest
 *
 * @created      16.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\SoundCloud;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property  \chillerlan\OAuth\Providers\SoundCloud $provider
 */
#[Group('providerLiveTest')]
class SoundcloudAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return SoundCloud::class;
	}

	protected function getEnvPrefix():string{
		return 'SOUNDCLOUD';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->username);
	}

	public function testRequestCredentialsToken():void{
		$this::markTestSkipped('may fail because SoundCloud deleted older applications');
	}

}
