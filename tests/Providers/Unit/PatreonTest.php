<?php
/**
 * Class PatreonTest
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Providers\{Patreon, ProviderException};

/**
 * @property \chillerlan\OAuth\Providers\Patreon $provider
 */
final class PatreonTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Patreon::class;
	}

	public function testMeInvalidScopesException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid scopes for the identity endpoint');

		$this->provider
			->storeAccessToken(new AccessToken(['expires' => 42]))
			->me()
		;
	}

}
