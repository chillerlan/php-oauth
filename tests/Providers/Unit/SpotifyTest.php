<?php
/**
 * Class SpotifyTest
 *
 * @created      06.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Spotify;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Spotify $provider
 */
#[Provider(Spotify::class)]
final class SpotifyTest extends OAuth2ProviderUnitTestAbstract{

}
