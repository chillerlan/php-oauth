<?php
/**
 * Class TwitterTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Twitter;

/**
 * @property \chillerlan\OAuth\Providers\Twitter $provider
 */
final class TwitterTest extends OAuth1ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Twitter::class;
	}

}
