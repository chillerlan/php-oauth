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
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\TwitterCC $provider
 */
#[Group('providerLiveTest')]
final class TwitterCCAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return TwitterCC::class;
	}

	public function testMeUnauthorizedAccessException():void{
		$this::markTestSkipped('N/A');
	}

}
