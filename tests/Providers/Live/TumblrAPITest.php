<?php
/**
 * Class TumblrTest
 *
 * @created      24.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Tumblr;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property  \chillerlan\OAuth\Providers\Tumblr $provider
 */
#[Group('providerLiveTest')]
class TumblrAPITest extends OAuth1ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Tumblr::class;
	}

	protected function getEnvPrefix():string{
		return 'TUMBLR';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->response->user->name);
	}

	public function testTokenExchange():void{
		// only outcomment if wou want to deliberately invaildate your current token
		$this::markTestSkipped('N/A - will invalidate the current token');

#		$this::assertSame('bearer', $this->provider->exchangeForOAuth2Token()->extraParams['token_type']);
	}

}
