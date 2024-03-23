<?php
/**
 * Class GoogleAPITest
 *
 * @created      09.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Google;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\Google $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
class GoogleAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Google::class;
	}

	protected function getEnvPrefix():string{
		return 'GOOGLE';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->email);
	}

}
