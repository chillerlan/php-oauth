<?php
/**
 * Class DeezerTest
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\HTTP\Utils\QueryUtil;
use chillerlan\OAuth\Providers\Deezer;
use chillerlan\OAuth\Providers\ProviderException;
use function implode;

/**
 * @property \chillerlan\OAuth\Providers\Deezer $provider
 */
class DeezerTest extends OAuth2ProviderUnitTestAbstract{

	protected const TEST_TOKEN = 'access_token=2YotnFZFEjr1zCsicMWpAA&token_type=example&expires=3600&'.
	                             'refresh_token=tGzv3JOkF0XG5Qx2TlKWIA&example_parameter=example_value';

	protected function getProviderFQCN():string{
		return Deezer::class;
	}

	public function testParseTokenResponseWithScopes():void{
		$this::markTestSkipped('N/A');
	}

	public function testParseTokenResponseNoDataException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$response = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream(''))
		;

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}

	public function testParseTokenResponseErrorException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token');

		// the error variable for deezer is "error_reason" and content type is form-data
		$response = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream('error_reason=whatever'))
		;

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}

	public function testParseTokenResponseNoTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('token missing');

		$response = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream('foo=bar'))
		;

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}

	public function testGetAuthURL():void{
		$params = ['response_type' => 'whatever', 'foo' => 'bar']; // response_type shall be overwritten
		$scopes = ['scope1', 'scope2', 'scope3'];

		$uri    = $this->provider->getAuthURL($params, $scopes);
		$params = QueryUtil::parse($uri->getQuery());

		$this::assertSame($this->getReflectionProperty('authURL'), (string)$uri->withQuery(''));

		$this::assertSame($this->options->key, $params['app_id']);
		$this::assertSame($this->options->callbackURL, $params['redirect_uri']);
		$this::assertSame(implode($this->provider::SCOPE_DELIMITER, $scopes), $params['perms']);
		$this::assertSame('bar', $params['foo']);
		$this::assertArrayHasKey('state', $params);

	}

	public function testGetAuthURLRequestParams():void{
		$params = ['foo' => 'bar']; // response_type shall be overwritten
		$scopes = ['scope1', 'scope2', 'scope3'];

		$queryparams = $this->invokeReflectionMethod('getAuthURLRequestParams', [$params, $scopes]);

		$this::assertArrayHasKey('app_id', $queryparams);
		$this::assertArrayHasKey('redirect_uri', $queryparams);
		$this::assertSame(implode($this->provider::SCOPE_DELIMITER, $scopes), $queryparams['perms']);
		$this::assertSame('bar', $queryparams['foo']);
	}

	public function testGetAccessTokenRequestBodyParams():void{
		$params = $this->invokeReflectionMethod('getAccessTokenRequestBodyParams', ['*test_code*']);

		$this::assertSame('*test_code*', $params['code']);
		$this::assertSame($this->options->key, $params['app_id']);
		$this::assertSame($this->options->secret, $params['secret']);
	}

}
