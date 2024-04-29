<?php
/**
 * Class DiscordAPITest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Providers\Discord;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Discord $provider
 */
#[Group('providerLiveTest')]
#[Provider(Discord::class)]
final class DiscordAPITest extends OAuth2ProviderLiveTestAbstract{

	public function testRequestCredentialsToken():void{
		$token = $this->provider->getClientCredentialsToken([Discord::SCOPE_CONNECTIONS, Discord::SCOPE_IDENTIFY]);

		$this::assertInstanceOf(AccessToken::class, $token);
		$this::assertIsString($token->accessToken);

		if($token->expires !== AccessToken::NEVER_EXPIRES){
			$this::assertGreaterThan(time(), $token->expires);
		}

		$this->logger->debug('APITestSupportsOAuth2ClientCredentials', $token->toArray());
	}

}
