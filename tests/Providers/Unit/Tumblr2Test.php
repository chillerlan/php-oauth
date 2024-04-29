<?php
/**
 * Class Tumblr2Test
 *
 * @created      30.07.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Tumblr2;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Tumblr2 $provider
 */
#[Provider(Tumblr2::class)]
final class Tumblr2Test extends OAuth2ProviderUnitTestAbstract{

}
