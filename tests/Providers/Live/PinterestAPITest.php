<?php
/**
 * Class PinterestAPITest
 *
 * @created      09.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Pinterest;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Pinterest $provider
 */
#[Group('providerLiveTest')]
#[Provider(Pinterest::class)]
class PinterestAPITest extends OAuth2ProviderLiveTestAbstract{

}
