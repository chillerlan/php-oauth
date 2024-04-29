<?php
/**
 * Class TwitchTest
 *
 * @created      15.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Twitch;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property  \chillerlan\OAuth\Providers\Twitch $provider
 */
#[Group('providerLiveTest')]
#[Provider(Twitch::class)]
final class TwitchAPITest extends OAuth2ProviderLiveTestAbstract{

}
