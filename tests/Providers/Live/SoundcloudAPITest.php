<?php
/**
 * Class SoundcloudAPITest
 *
 * @created      16.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\SoundCloud;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property  \chillerlan\OAuth\Providers\SoundCloud $provider
 */
#[Group('providerLiveTest')]
#[Provider(SoundCloud::class)]
final class SoundcloudAPITest extends OAuth2ProviderLiveTestAbstract{

	public function testRequestCredentialsToken():void{
		$this::markTestSkipped('may fail because SoundCloud deleted older applications');
	}

}
