<?php
/**
 * Class SlackAPITest
 *
 * @created      20.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Slack;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\Slack $provider
 */
#[Group('providerLiveTest')]
class SlackAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Slack::class;
	}

	protected function getEnvPrefix():string{
		return 'SLACK';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->user->email);
	}

}
