<?php
/**
 * Class OpenStreetmapTest
 *
 * @created      12.05.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\OpenStreetmap;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\OpenStreetmap $provider
 */
#[Provider(OpenStreetmap::class)]
final class OpenStreetmapTest extends OAuth1ProviderUnitTestAbstract{

}
