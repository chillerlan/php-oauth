<?php
/**
 * Class TwitterCCAPITest
 *
 * @created      26.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\TwitterCC;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\TwitterCC $provider
 */
#[Group('providerLiveTest')]
#[Provider(TwitterCC::class)]
final class TwitterCCAPITest extends OAuth2ProviderLiveTestAbstract{

	public function testMeUnauthorizedAccessException():void{
		$this::markTestSkipped('N/A');
	}

}
