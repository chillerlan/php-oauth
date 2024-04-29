<?php
/**
 * Class OpenStreetmapTest
 *
 * @created      05.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\OpenStreetmap2;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\OpenStreetmap $provider
 */
#[Provider(OpenStreetmap2::class)]
final class OpenStreetmap2Test extends OAuth2ProviderUnitTestAbstract{

}
