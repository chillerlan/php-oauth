<?php
/**
 * Class SlackAPITest
 *
 * @created      20.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AuthenticatedUser;
use chillerlan\OAuth\Providers\Slack;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Slack $provider
 */
#[Group('providerLiveTest')]
final class SlackAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Slack::class;
	}

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertSame($this->TEST_USER, $user->email);
	}

}
