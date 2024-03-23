<?php
/**
 * Class BitbucketTest
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Bitbucket;

/**
 * @property \chillerlan\OAuth\Providers\Bitbucket $provider
 */
final class BitbucketTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Bitbucket::class;
	}

}
