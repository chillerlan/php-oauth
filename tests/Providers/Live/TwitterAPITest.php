<?php
/**
 * Class TwitterAPITest
 *
 * @created      11.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Twitter;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Twitter $provider
 */
#[Group('providerLiveTest')]
#[Provider(Twitter::class)]
final class TwitterAPITest extends OAuth1ProviderLiveTestAbstract{

	protected string $screen_name;
	protected int $user_id;

}
