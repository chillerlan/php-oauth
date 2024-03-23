<?php
/**
 * Class DeviantArtTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\DeviantArt;

/**
 * @property \chillerlan\OAuth\Providers\DeviantArt $provider
 */
class DeviantArtTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return DeviantArt::class;
	}

	public function testTokenInvalidate():void{
		$this::markTestIncomplete();
	}

}
