<?php
/**
 * Class TwitchTest
 *
 * @created      15.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Twitch;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property  \chillerlan\OAuth\Providers\Twitch $provider
 */
#[Group('providerLiveTest')]
class TwitchAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Twitch::class;
	}

	protected function getEnvPrefix():string{
		return 'TWITCH';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->data[0]->display_name);
	}

}
