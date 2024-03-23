<?php
/**
 * Class MusicBrainzTest
 *
 * @created      31.07.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\TokenRefresh;
use chillerlan\OAuth\Providers\MusicBrainz;

/**
 * @property \chillerlan\OAuth\Providers\MusicBrainz $provider
 */
final class MusicBrainzTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return MusicBrainz::class;
	}

	public function testGetRefreshAccessTokenRequestBodyParams():void{

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
		}

		$params = $this->invokeReflectionMethod('getRefreshAccessTokenRequestBodyParams', ['*refresh_token*']);

		$this::assertSame('*refresh_token*', $params['refresh_token']);
		$this::assertSame($this->options->key, $params['client_id']);
		$this::assertSame($this->options->secret, $params['client_secret']);
		$this::assertSame('refresh_token', $params['grant_type']);
	}

}
