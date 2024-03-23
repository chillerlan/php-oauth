<?php
/**
 * Class DeezerAPITest
 *
 * @created      10.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Deezer;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\Deezer $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
class DeezerAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Deezer::class;
	}

	protected function getEnvPrefix():string{
		return 'DEEZER';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->name);
	}

}
