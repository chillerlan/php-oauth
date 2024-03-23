<?php
/**
 * Class GuildWars2APITest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Providers\GuildWars2;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\GuildWars2 $provider
 */
#[Group('providerLiveTest')]
class GuildWars2APITest extends OAuth2ProviderLiveTestAbstract{

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

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->tokenname, $json->name);
	}

}
