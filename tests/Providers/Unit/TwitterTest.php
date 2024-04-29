<?php
/**
 * Class TwitterTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Twitter;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Twitter $provider
 */
#[Provider(Twitter::class)]
final class TwitterTest extends OAuth1ProviderUnitTestAbstract{

}
