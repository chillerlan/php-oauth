<?php
/**
 * Class MixcloudTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Mixcloud;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Mixcloud $provider
 */
#[Provider(Mixcloud::class)]
final class MixcloudTest extends OAuth2ProviderUnitTestAbstract{

}
