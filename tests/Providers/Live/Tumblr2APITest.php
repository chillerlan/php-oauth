<?php
/**
 * Class Tumblr2APITest
 *
 * @created      30.07.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Tumblr2;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\Tumblr2 $provider
 */
#[Group('providerLiveTest')]
class Tumblr2APITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Tumblr2::class;
	}

	protected function getEnvPrefix():string{
		return 'TUMBLR';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->response->user->name);
	}

}
