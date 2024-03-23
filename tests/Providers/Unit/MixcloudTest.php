<?php
/**
 * Class MixcloudTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Mixcloud;

/**
 * @property \chillerlan\OAuth\Providers\Mixcloud $provider
 */
final class MixcloudTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Mixcloud::class;
	}

}
