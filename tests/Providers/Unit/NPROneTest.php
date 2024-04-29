<?php
/**
 * Class NPROneTest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\NPROne;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\NPROne $provider
 */
#[Provider(NPROne::class)]
final class NPROneTest extends OAuth2ProviderUnitTestAbstract{

	public function testSetAPI():void{
		$this::assertSame('https://listening.api.npr.org', $this->getReflectionProperty('apiURL'));

		$this->provider->setAPI('station');

		$this::assertSame('https://station.api.npr.org', $this->getReflectionProperty('apiURL'));
	}

}
