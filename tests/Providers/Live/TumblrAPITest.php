<?php
/**
 * Class TumblrTest
 *
 * @created      24.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Tumblr;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property  \chillerlan\OAuth\Providers\Tumblr $provider
 */
#[Group('providerLiveTest')]
#[Provider(Tumblr::class)]
final class TumblrAPITest extends OAuth1ProviderLiveTestAbstract{

	public function testTokenExchange():void{
		// only outcomment if wou want to deliberately invaildate your current token
		$this::markTestSkipped('N/A - will invalidate the current token');

#		$this::assertSame('bearer', $this->provider->exchangeForOAuth2Token()->extraParams['token_type']);
	}

}
