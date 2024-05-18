<?php
/**
 * Class SteamAPITest
 *
 * @created      15.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AuthenticatedUser;
use chillerlan\OAuth\Providers\Steam;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Steam $provider
 */
#[Group('providerLiveTest')]
#[Provider(Steam::class)]
final class SteamAPITest extends OAuthProviderLiveTestAbstract{

	protected int $id;

	protected function setUp():void{
		parent::setUp();

		$token = $this->storage->getAccessToken($this->provider->getName());

		$this->id = $token->extraParams['id_int']; // SteamID64
	}

	public function testMeUnauthorizedAccessException():void{
		$this::markTestSkipped('N/A');
	}

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertSame((int)$this->TEST_USER, $user->id);
	}

}
