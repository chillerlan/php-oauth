<?php
/**
 * Class VimeoTest
 *
 * @created      09.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Vimeo;

/**
 * @property \chillerlan\OAuth\Providers\Vimeo $provider
 */
class VimeoTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Vimeo::class;
	}

	public function testTokenInvalidate():void{
		$this::markTestIncomplete();
	}

}
