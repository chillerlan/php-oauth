<?php
/**
 * Class GitLabTest
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\GitLab;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\GitLab $provider
 */
#[Provider(GitLab::class)]
final class GitLabTest extends OAuth2ProviderUnitTestAbstract{

}
