<?php
/**
 * Class DiscordAPITest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Providers\Discord;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\Discord $provider
 */
#[Group('providerLiveTest')]
class DiscordAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Discord::class;
	}

	protected function getEnvPrefix():string{
		return 'DISCORD';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->username);
	}

	public function testRequestCredentialsToken():void{
		$token = $this->provider->getClientCredentialsToken([Discord::SCOPE_CONNECTIONS, Discord::SCOPE_IDENTIFY]);

		$this::assertInstanceOf(AccessToken::class, $token);
		$this::assertIsString($token->accessToken);

		if($token->expires !== AccessToken::EOL_NEVER_EXPIRES){
			$this::assertGreaterThan(time(), $token->expires);
		}

		$this->logger->debug('APITestSupportsOAuth2ClientCredentials', $token->toArray());
	}

}
