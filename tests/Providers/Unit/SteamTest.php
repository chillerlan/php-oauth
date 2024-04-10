<?php
/**
 * Class SteamTest
 *
 * @created      15.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Steam;

/**
 * @property \chillerlan\OAuth\Providers\Steam $provider
 */
final class SteamTest extends OAuthProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Steam::class;
	}

	public function testMeUnknownErrorException():void{
		$this->markTestSkipped('N/A');
	}

}
