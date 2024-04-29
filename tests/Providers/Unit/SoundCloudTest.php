<?php
/**
 * Class SoundCloudTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\SoundCloud;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\SoundCloud $provider
 */
#[Provider(SoundCloud::class)]
final class SoundCloudTest extends OAuth2ProviderUnitTestAbstract{

}
