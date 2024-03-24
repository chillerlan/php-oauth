<?php
/**
 * Class TwitterAPITest
 *
 * @created      11.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Twitter;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Twitter $provider
 */
#[Group('providerLiveTest')]
class TwitterAPITest extends OAuth1ProviderLiveTestAbstract{

	protected string $screen_name;
	protected int $user_id;

	protected function getProviderFQCN():string{
		return Twitter::class;
	}

	protected function getEnvPrefix():string{
		return 'TWITTER';
	}

}
