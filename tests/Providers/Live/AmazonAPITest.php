<?php
/**
 * Class AmazonAPITest
 *
 * @created      10.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Providers\Amazon;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\Amazon $provider
 */
#[Group('providerLiveTest')]
class AmazonAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Amazon::class;
	}

	protected function getEnvPrefix():string{
		return 'AMAZON';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertMatchesRegularExpression('/[a-z\d.]+/i', $json->user_id);
	}

	public function testMeErrorException():void{
		$token                    = $this->storage->getAccessToken($this->provider->serviceName);
		// avoid refresh
		$token->expires           = AccessToken::EOL_NEVER_EXPIRES;
		$token->refreshToken      = null;
		// invalidate token
		$token->accessToken       = 'Atza|nope'; // amazon tokens are prefixed

		$this->assertMeErrorException($token);
	}

}
