<?php
/**
 * Class BigCartelAPITest
 *
 * @created      10.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\BigCartel;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\BigCartel $provider
 */
#[Group('providerLiveTest')]
#[Provider(BigCartel::class)]
final class BigCartelAPITest extends OAuth2ProviderLiveTestAbstract{

}
