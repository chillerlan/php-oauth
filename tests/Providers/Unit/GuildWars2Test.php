<?php
/**
 * Class GuildWars2Test
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\GuildWars2;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\GuildWars2 $provider
 */
#[Provider(GuildWars2::class)]
final class GuildWars2Test extends OAuth2ProviderUnitTestAbstract{

	public function testGetAuthURL():void{
		$this::markTestSkipped('N/A');
	}

	public function testGetAccessToken():void{
		$this::markTestSkipped('N/A');
	}

}
