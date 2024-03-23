<?php
/**
 * Class GitHubTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\GitHub;

/**
 * @property \chillerlan\OAuth\Providers\GitHub $provider
 */
final class GitHubTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return GitHub::class;
	}

}
