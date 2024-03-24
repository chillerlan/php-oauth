<?php
/**
 * Class BitbucketAPITest
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Bitbucket;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Bitbucket $provider
 */
#[Group('providerLiveTest')]
class BitbucketAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Bitbucket::class;
	}

	protected function getEnvPrefix():string{
		return 'BITBUCKET';
	}

}
