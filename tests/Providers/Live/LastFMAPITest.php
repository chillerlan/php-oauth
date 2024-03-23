<?php
/**
 * Class LastFMAPITest
 *
 * @created      10.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\LastFM;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\LastFM $provider
 */
#[Group('providerLiveTest')]
class LastFMAPITest extends OAuthProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return LastFM::class;
	}

	protected function getEnvPrefix():string{
		return 'LASTFM';
	}

	protected function setUp():void{
		parent::setUp();

		// username is stored in the session token
		$token           = $this->storage->getAccessToken($this->provider->serviceName);
		$this->TEST_USER = $token->extraParams['session']['name'];
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->user->name);
	}

}
