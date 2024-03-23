<?php
/**
 * Class GuildWars2Test
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\GuildWars2;

/**
 * @property \chillerlan\OAuth\Providers\GuildWars2 $provider
 */
class GuildWars2Test extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return GuildWars2::class;
	}

	public function testGetAuthURL():void{
		$this::markTestSkipped('N/A');
	}

}
