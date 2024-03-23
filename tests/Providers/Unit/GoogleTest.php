<?php
/**
 * Class GoogleTest
 *
 * @created      09.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Google;

/**
 * @property \chillerlan\OAuth\Providers\Google $provider
 */
final class GoogleTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Google::class;
	}

}
