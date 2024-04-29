<?php
/**
 * Class GitHubTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\GitHub;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\GitHub $provider
 */
#[Provider(GitHub::class)]
final class GitHubTest extends OAuth2ProviderUnitTestAbstract{

}
