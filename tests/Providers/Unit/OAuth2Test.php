<?php
/**
 * Class OAuth2Test
 *
 * @created      16.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuthTest\Attributes\Provider;
use chillerlan\OAuthTest\Providers\DummyOAuth2Provider;

/**
 * The built-in dummy test for OAuth2
 *
 * @property \chillerlan\OAuthTest\Providers\DummyOAuth2Provider $provider
 */
#[Provider(DummyOAuth2Provider::class)]
final class OAuth2Test extends OAuth2ProviderUnitTestAbstract{

	public function testMeUnknownErrorException():void{
		$this->markTestSkipped('N/A');
	}

}
