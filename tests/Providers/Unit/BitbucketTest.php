<?php
/**
 * Class BitbucketTest
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Bitbucket;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Bitbucket $provider
 */
#[Provider(Bitbucket::class)]
final class BitbucketTest extends OAuth2ProviderUnitTestAbstract{

}
