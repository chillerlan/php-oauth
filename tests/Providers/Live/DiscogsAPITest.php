<?php
/**
 * Class DiscogsAPITest
 *
 * @created      10.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Discogs;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\Discogs $provider
 */
#[Group('providerLiveTest')]
class DiscogsAPITest extends OAuth1ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Discogs::class;
	}

	protected function getEnvPrefix():string{
		return 'DISCOGS';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->username);
	}

}
