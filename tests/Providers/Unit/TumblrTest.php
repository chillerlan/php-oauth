<?php
/**
 * Class TumblrTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Tumblr;

/**
 * @property \chillerlan\OAuth\Providers\Tumblr $provider
 */
final class TumblrTest extends OAuth1ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Tumblr::class;
	}

}
