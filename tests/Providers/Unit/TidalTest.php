<?php
/**
 * Class TidalTest
 *
 * @created      04.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Tidal;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Tidal $provider
 */
#[Provider(Tidal::class)]
class TidalTest extends OAuth2ProviderUnitTestAbstract{

}
