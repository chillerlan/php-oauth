<?php
/**
 * Class FlickrTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Flickr;

/**
 * @property \chillerlan\OAuth\Providers\Flickr $provider
 */
class FlickrTest extends OAuth1ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Flickr::class;
	}

}
