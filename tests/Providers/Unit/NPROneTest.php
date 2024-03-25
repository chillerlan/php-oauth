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

/**
 * @property \chillerlan\OAuth\Providers\NPROne $provider
 */
final class NPROneTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return NPROne::class;
	}

	public function testSetAPI():void{
		$this::assertSame('https://listening.api.npr.org', $this->getReflectionProperty('apiURL'));

		$this->provider->setAPI('station');

		$this::assertSame('https://station.api.npr.org', $this->getReflectionProperty('apiURL'));
	}

}
