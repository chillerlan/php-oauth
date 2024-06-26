<?php
/**
 * Class DiscordTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Discord;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Discord $provider
 */
#[Provider(Discord::class)]
final class DiscordTest extends OAuth2ProviderUnitTestAbstract{

}
