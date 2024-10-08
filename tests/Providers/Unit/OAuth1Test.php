<?php
/**
 * Class OAuth1Test
 *
 * @created      16.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuthTest\Attributes\Provider;
use chillerlan\OAuthTest\Providers\DummyOAuth1Provider;

/**
 * The built-in dummy test for OAuth1
 *
 * @property \chillerlan\OAuthTest\Providers\DummyOAuth1Provider $provider
 */
#[Provider(DummyOAuth1Provider::class)]
final class OAuth1Test extends OAuth1ProviderUnitTestAbstract{

	public function testMeUnknownErrorException():void{
		$this->markTestSkipped('N/A');
	}

	public function testTokenInvalidateFailedWithException():void{
		$this->markTestSkipped('N/A');
	}

}
