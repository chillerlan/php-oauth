<?php
/**
 * Class PatreonAPITest
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Patreon;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\Patreon $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
class PatreonAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Patreon::class;
	}

	protected function getEnvPrefix():string{
		return 'PATREON';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->data->attributes->email);
	}

}
