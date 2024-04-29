<?php
/**
 * Class AmazonTest
 *
 * @created      10.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Amazon;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Amazon $provider
 */
#[Provider(Amazon::class)]
final class AmazonTest extends OAuth2ProviderUnitTestAbstract{

}
