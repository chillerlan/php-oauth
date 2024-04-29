<?php
/**
 * Class TwitterCCTest
 *
 * @created      26.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\TwitterCC;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\TwitterCC $provider
 */
#[Provider(TwitterCC::class)]
final class TwitterCCTest extends OAuth2ProviderUnitTestAbstract{

	public function testGetAuthURL():void{
		$this->markTestSkipped('N/A');
	}

	public function testGetAccessToken():void{
		$this->markTestSkipped('N/A');
	}

	public function testMeUnknownErrorException():void{
		$this->markTestSkipped('N/A');
	}

}
