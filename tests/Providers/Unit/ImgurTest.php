<?php
/**
 * Class ImgurTest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Imgur;

/**
 * @property \chillerlan\OAuth\Providers\Imgur $provider
 */
final class ImgurTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Imgur::class;
	}

}
