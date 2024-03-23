<?php
/**
 * Class TwitterAPITest
 *
 * @created      11.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Twitter;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\Twitter $provider
 */
#[Group('providerLiveTest')]
class TwitterAPITest extends OAuth1ProviderLiveTestAbstract{

	protected string $screen_name;
	protected int $user_id;

	protected function getProviderFQCN():string{
		return Twitter::class;
	}

	protected function getEnvPrefix():string{
		return 'TWITTER';
	}

	protected function setUp():void{
		parent::setUp();

		$token             = $this->storage->getAccessToken($this->provider->serviceName);
		$this->screen_name = $token->extraParams['screen_name'];
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->screen_name, $json->screen_name);
	}

}
