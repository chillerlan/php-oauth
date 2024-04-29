<?php
/**
 * Class DiscogsTest
 *
 * @created      05.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Discogs;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Discogs $provider
 */
#[Provider(Discogs::class)]
final class DiscogsTest extends OAuth1ProviderUnitTestAbstract{

}
