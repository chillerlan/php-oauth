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
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Flickr $provider
 */
#[Provider(Flickr::class)]
final class FlickrTest extends OAuth1ProviderUnitTestAbstract{

	public function testRequest():void{
		$this::markTestIncomplete();
	}

	public function testRequestUnauthorizedException():void{
		$this::markTestIncomplete();
	}

}
