<?php
/**
 * Class TumblrTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Tumblr;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Tumblr $provider
 */
#[Provider(Tumblr::class)]
final class TumblrTest extends OAuth1ProviderUnitTestAbstract{

}
