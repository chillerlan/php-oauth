<?php
/**
 * Class PatreonAPITest
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Patreon;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Patreon $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
#[Provider(Patreon::class)]
final class PatreonAPITest extends OAuth2ProviderLiveTestAbstract{

}
