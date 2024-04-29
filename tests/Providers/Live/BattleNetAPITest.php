<?php
/**
 * Class BattleNetAPITest
 *
 * @created      03.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AuthenticatedUser;
use chillerlan\OAuth\Providers\BattleNet;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;
use function explode;

/**
 * @property \chillerlan\OAuth\Providers\BattleNet $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
#[Provider(BattleNet::class)]
final class BattleNetAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertSame($this->TEST_USER, explode('#', $user->handle)[0]);
	}

}
