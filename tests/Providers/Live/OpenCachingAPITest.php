<?php
/**
 * Class OpenCachingAPITest
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\OpenCaching;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\OpenCaching $provider
 */
#[Group('providerLiveTest')]
#[Provider(OpenCaching::class)]
final class OpenCachingAPITest extends OAuth1ProviderLiveTestAbstract{

}
