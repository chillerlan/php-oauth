<?php
/**
 * Class AmazonTest
 *
 * @created      10.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Amazon;

/**
 * @property \chillerlan\OAuth\Providers\Amazon $provider
 */
final class AmazonTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Amazon::class;
	}

}
