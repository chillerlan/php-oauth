<?php
/**
 * Class TwitchTest
 *
 * @created      15.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Twitch;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property  \chillerlan\OAuth\Providers\Twitch $provider
 */
#[Group('providerLiveTest')]
class TwitchAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Twitch::class;
	}

	protected function getEnvPrefix():string{
		return 'TWITCH';
	}

}
