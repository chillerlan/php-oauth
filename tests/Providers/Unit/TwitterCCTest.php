<?php
/**
 * Class TwitterCCTest
 *
 * @created      26.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\TwitterCC;

/**
 * @property \chillerlan\OAuth\Providers\TwitterCC $provider
 */
class TwitterCCTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return TwitterCC::class;
	}

	public function testGetAuthURL():void{
		$this->markTestSkipped('N/A');
	}

	public function testGetAccessToken():void{
		$this->markTestSkipped('N/A');
	}

}
