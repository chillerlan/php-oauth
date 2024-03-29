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
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\GuildWars2 $provider
 */
#[Group('providerLiveTest')]
final class GuildWars2APITest extends OAuth2ProviderLiveTestAbstract{

	protected AccessToken $token;
	protected string      $tokenname;

	protected function getProviderFQCN():string{
		return GuildWars2::class;
	}

	protected function getEnvPrefix():string{
		return '';
	}

	protected function setUp():void{
		parent::setUp();

		$tokenfile = $this->CFG_DIR.'/'.$this->provider->servicename.'.token.json';

		$this->token = !file_exists($tokenfile)
			? $this->provider->storeGW2Token($this->dotEnv->GW2_TOKEN)
			: (new AccessToken)->fromJSON(file_get_contents($tokenfile));

		$this->tokenname = $this->dotEnv->GW2_TOKEN_NAME;
	}

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertSame($this->tokenname, $user->handle);
	}

}
