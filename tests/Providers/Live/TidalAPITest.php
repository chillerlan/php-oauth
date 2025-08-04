<?php
/**
 * Class TidalAPITest
 *
 * @created      04.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Tidal;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Tidal $provider
 */
#[Group('providerLiveTest')]
#[Provider(Tidal::class)]
class TidalAPITest extends OAuth2ProviderLiveTestAbstract{

}
