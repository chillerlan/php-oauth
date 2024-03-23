<?php
/**
 * Class MastodonTest
 *
 * @created      19.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\OAuthException;
use chillerlan\OAuth\Providers\Mastodon;

/**
 * @property \chillerlan\OAuth\Providers\Mastodon $provider
 */
class MastodonTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Mastodon::class;
	}

	public function testSetInvalidInstance():void{
		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage('invalid instance URL');

		$this->provider->setInstance('whatever');
	}

}
