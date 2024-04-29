<?php
/**
 * Class YouTubeTest
 *
 * @created      09.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\YouTube;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\YouTube $provider
 */
#[Provider(YouTube::class)]
final class YouTubeTest extends OAuth2ProviderUnitTestAbstract{

}
