<?php
/**
 * Class VimeoAPITest
 *
 * @created      09.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Vimeo;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Vimeo $provider
 */
#[Group('providerLiveTest')]
#[Provider(Vimeo::class)]
final class VimeoAPITest extends OAuth2ProviderLiveTestAbstract{

}
