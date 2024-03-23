<?php
/**
 * Class SteamOpenIDAPITest
 *
 * @created      15.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\SteamOpenID;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\SteamOpenID $provider
 */
#[Group('providerLiveTest')]
class SteamOpenIDAPITest extends OAuthProviderLiveTestAbstract{

	protected int $id;

	protected function getProviderFQCN():string{
		return SteamOpenID::class;
	}

	protected function getEnvPrefix():string{
		return 'STEAMOPENID';
	}

	protected function setUp():void{
		parent::setUp();

		$token = $this->storage->getAccessToken($this->provider->serviceName);

		$this->id = $token->extraParams['id_int']; // SteamID64
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		// noop
	}

	public function testMe():void{
		$this::markTestSkipped('user endpoint N/A');
	}

	public function testMeErrorException():void{
		$this::markTestSkipped('not implemented');
	}

}
