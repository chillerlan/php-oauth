<?php
/**
 * Class ImgurAPITest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Imgur;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Imgur $provider
 */
#[Group('providerLiveTest')]
#[Provider(Imgur::class)]
final class ImgurAPITest extends OAuth2ProviderLiveTestAbstract{

}
