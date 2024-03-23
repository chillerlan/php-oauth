<?php
/**
 * Class BigCartelTest
 *
 * @created      10.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\BigCartel;

/**
 * @property \chillerlan\OAuth\Providers\BigCartel $provider
 */
class BigCartelTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return BigCartel::class;
	}

	public function testTokenInvalidate():void{
		$this::markTestIncomplete();
	}

}
