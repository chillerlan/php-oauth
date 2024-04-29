<?php
/**
 * Class ImgurTest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Imgur;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Imgur $provider
 */
#[Provider(Imgur::class)]
final class ImgurTest extends OAuth2ProviderUnitTestAbstract{

}
