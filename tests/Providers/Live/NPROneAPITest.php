<?php
/**
 * Class NPROneAPITest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\NPROne;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\NPROne $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
class NPROneAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return NPROne::class;
	}

	protected function getEnvPrefix():string{
		return 'NPRONE';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->attributes->email);
	}

}
