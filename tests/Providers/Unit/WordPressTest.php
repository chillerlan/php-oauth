<?php
/**
 * Class WordPressTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\WordPress;

/**
 * @property \chillerlan\OAuth\Providers\WordPress $provider
 */
final class WordPressTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return WordPress::class;
	}

}
