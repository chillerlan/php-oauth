<?php
/**
 * Class GuildWars2APITest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Core\AuthenticatedUser;
use chillerlan\OAuth\Providers\GuildWars2;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\GuildWars2 $provider
 */
#[Group('providerLiveTest')]
#[Provider(GuildWars2::class)]
final class GuildWars2APITest extends OAuth2ProviderLiveTestAbstract{

	protected AccessToken $token;
	protected string      $tokenname;

	protected function setUp():void{
		parent::setUp();

		$this->provider->storeGW2Token($this->dotEnv->GW2_TOKEN);

		$this->tokenname = $this->dotEnv->GW2_TOKEN_NAME;
	}

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertSame($this->tokenname, $user->handle);
	}

}
