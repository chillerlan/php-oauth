<?php
/**
 * Class SteamOpenIDTest
 *
 * @created      15.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\SteamOpenID;

/**
 * @property \chillerlan\OAuth\Providers\SteamOpenID $provider
 */
final class SteamOpenIDTest extends OAuthProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return SteamOpenID::class;
	}

	public function testMeUnknownErrorException():void{
		$this->markTestSkipped('N/A');
	}

}
