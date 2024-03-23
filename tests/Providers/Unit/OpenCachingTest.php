<?php
/**
 * Class OpenCachingTest
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\OpenCaching;

/**
 * @property \chillerlan\OAuth\Providers\OpenCaching $provider
 */
final class OpenCachingTest extends OAuth1ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return OpenCaching::class;
	}

}
