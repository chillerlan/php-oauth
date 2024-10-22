<?php
/**
 * Class TikTokTest
 *
 * @created      18.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\HTTP\Utils\QueryUtil;
use chillerlan\OAuth\Providers\TikTok;
use function implode;

/**
 * @property \chillerlan\OAuth\Providers\TikTok $provider
 */
final class TikTokTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return TikTok::class;
	}

	public function testGetAuthURL():void{
		$uri    = $this->provider->getAuthorizationURL();
		$params = QueryUtil::parse($uri->getQuery());

		$this::assertSame($this->getReflectionProperty('authorizationURL'), (string)$uri->withQuery(''));

		$this::assertArrayHasKey('client_key', $params);
		$this::assertArrayHasKey('redirect_uri', $params);
		$this::assertArrayHasKey('response_type', $params);

		$this::assertArrayHasKey('state', $params);
	}

	public function testGetAuthURLRequestParams():void{
		$extraparams = ['response_type' => 'whatever', 'foo' => 'bar'];
		$scopes      = ['scope1', 'scope2', 'scope3'];

		$params = $this->invokeReflectionMethod('getAuthorizationURLRequestParams', [$extraparams, $scopes]);

		$this::assertSame($this->options->key, $params['client_key']);
		$this::assertSame($this->options->callbackURL, $params['redirect_uri']);
		$this::assertSame('code', $params['response_type']);
		$this::assertSame(implode($this->provider::SCOPES_DELIMITER, $scopes), $params['scope']);
		$this::assertSame('bar', $params['foo']);
	}

	public function testGetAccessTokenRequestBodyParams():void{
		$verifier = $this->provider->generateVerifier($this->options->pkceVerifierLength);

		$this->storage->storeCodeVerifier($verifier, $this->provider->name);

		$params = $this->invokeReflectionMethod('getAccessTokenRequestBodyParams', ['*test_code*']);

		$this::assertSame('*test_code*', $params['code']);
		$this::assertSame($this->options->callbackURL, $params['redirect_uri']);
		$this::assertSame('authorization_code', $params['grant_type']);
		$this::assertSame($this->options->key, $params['client_key']);
		$this::assertSame($this->options->secret, $params['client_secret']);

		$this::assertSame($verifier, $params['code_verifier']);

	}

	public function testGetRefreshAccessTokenRequestBodyParams():void{
		$params = $this->invokeReflectionMethod('getRefreshAccessTokenRequestBodyParams', ['*refresh_token*']);

		$this::assertSame('*refresh_token*', $params['refresh_token']);
		$this::assertSame($this->options->key, $params['client_key']);
		$this::assertSame($this->options->secret, $params['client_secret']);
		$this::assertSame('refresh_token', $params['grant_type']);
	}


}
