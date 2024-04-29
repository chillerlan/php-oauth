<?php
/**
 * Class MastodonTest
 *
 * @created      19.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\OAuthException;
use chillerlan\OAuth\Providers\Mastodon;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Mastodon $provider
 */
#[Provider(Mastodon::class)]
final class MastodonTest extends OAuth2ProviderUnitTestAbstract{

	public function testSetInvalidInstance():void{
		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage('invalid instance URL');

		$this->provider->setInstance('whatever');
	}

}
