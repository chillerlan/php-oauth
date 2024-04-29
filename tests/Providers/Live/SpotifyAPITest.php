<?php
/**
 * Class SpotifyAPITest
 *
 * @created      06.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Spotify;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Spotify $provider
 */
#[Group('providerLiveTest')]
#[Provider(Spotify::class)]
final class SpotifyAPITest extends OAuth2ProviderLiveTestAbstract{

}
