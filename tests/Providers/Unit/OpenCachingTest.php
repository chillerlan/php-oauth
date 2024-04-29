<?php
/**
 * Class OpenCachingTest
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\OpenCaching;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\OpenCaching $provider
 */
#[Provider(OpenCaching::class)]
final class OpenCachingTest extends OAuth1ProviderUnitTestAbstract{

}
