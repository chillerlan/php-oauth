<?php
/**
 * Class EtsyTest
 *
 * @created      08.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\TokenRefresh;
use chillerlan\OAuth\Providers\Etsy;

/**
 *
 */
class EtsyTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Etsy::class;
	}

	public function testGetAccessTokenRequestBodyParams():void{
		$verifier = $this->provider->generateVerifier($this->options->pkceVerifierLength);

		$this->storage->storeCodeVerifier($verifier, $this->provider->name);

		$params = $this->invokeReflectionMethod('getAccessTokenRequestBodyParams', ['*test_code*']);

		$this::assertSame('*test_code*', $params['code']);
		$this::assertSame($this->options->callbackURL, $params['redirect_uri']);
		$this::assertSame('authorization_code', $params['grant_type']);

		$this::assertSame($this->options->key, $params['client_id']);

		$this::assertSame($verifier, $params['code_verifier']);
	}

	public function testGetRefreshAccessTokenRequestBodyParams():void{
		$params = $this->invokeReflectionMethod('getRefreshAccessTokenRequestBodyParams', ['*refresh_token*']);

		$this::assertSame('*refresh_token*', $params['refresh_token']);
		$this::assertSame($this->options->key, $params['client_id']);
		$this::assertSame('refresh_token', $params['grant_type']);
	}

}
