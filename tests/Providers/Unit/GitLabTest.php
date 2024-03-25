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

/**
 * @property \chillerlan\OAuth\Providers\GitLab $provider
 */
final class GitLabTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return GitLab::class;
	}

}
