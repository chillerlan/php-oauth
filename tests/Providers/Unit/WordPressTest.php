<?php
/**
 * Class WordPressTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\WordPress;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\WordPress $provider
 */
#[Provider(WordPress::class)]
final class WordPressTest extends OAuth2ProviderUnitTestAbstract{

}
