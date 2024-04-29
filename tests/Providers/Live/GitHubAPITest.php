<?php
/**
 * Class GitHubAPITest
 *
 * @created      18.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\GitHub;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property  \chillerlan\OAuth\Providers\GitHub $provider
 */
#[Group('providerLiveTest')]
#[Provider(GitHub::class)]
final class GitHubAPITest extends OAuth2ProviderLiveTestAbstract{

}
