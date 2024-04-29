<?php
/**
 * Class DiscogsAPITest
 *
 * @created      10.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Discogs;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Discogs $provider
 */
#[Group('providerLiveTest')]
#[Provider(Discogs::class)]
final class DiscogsAPITest extends OAuth1ProviderLiveTestAbstract{

}
