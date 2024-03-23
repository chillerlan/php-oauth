<?php
/**
 * Class DeviantArtAPITest
 *
 * @created      27.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\DeviantArt;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\DeviantArt $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
class DeviantArtAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return DeviantArt::class;
	}

	protected function getEnvPrefix():string{
		return 'DEVIANTART';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->username);
	}

}
