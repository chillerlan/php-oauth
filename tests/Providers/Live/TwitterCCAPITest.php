<?php
/**
 * Class TwitterCCAPITest
 *
 * @created      26.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\TwitterCC;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\TwitterCC $provider
 */
#[Group('providerLiveTest')]
class TwitterCCAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return TwitterCC::class;
	}

	protected function getEnvPrefix():string{
		return 'TWITTER';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		// noop
	}

	public function testMe():void{
		$this::markTestSkipped('user endpoint N/A');
	}

	public function testMeErrorException():void{
		$this::markTestSkipped('not implemented');
	}

}
