<?php
/**
 * Class OAuth2ProviderUnitTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\{AccessToken, ClientCredentials, CSRFStateMismatchException, CSRFToken, OAuth2Interface, TokenRefresh};
use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\HTTP\Utils\QueryUtil;
use chillerlan\OAuth\OAuthException;
use chillerlan\OAuth\Providers\ProviderException;
use function base64_encode;
use function implode;
use function json_decode;
use function json_encode;

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
abstract class OAuth2ProviderUnitTestAbstract extends OAuthProviderUnitTestAbstract{

	public function testOAuth2Instance():void{
		$this::assertInstanceOf(OAuth2Interface::class, $this->provider);
	}

	// from https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.4
	protected const TEST_TOKEN = '{"access_token":"2YotnFZFEjr1zCsicMWpAA","token_type":"example","expires_in":3600,'.
	                             '"refresh_token":"tGzv3JOkF0XG5Qx2TlKWIA","example_parameter":"example_value"}';


	/*
	 * auth URL
	 */

	public function testGetAuthURL():void{
		$uri    = $this->provider->getAuthURL();
		$params = QueryUtil::parse($uri->getQuery());

		$this::assertSame($this->getReflectionProperty('authURL'), (string)$uri->withQuery(''));

		$this::assertArrayHasKey('client_id', $params);
		$this::assertArrayHasKey('redirect_uri', $params);
		$this::assertArrayHasKey('response_type', $params);
		$this::assertArrayHasKey('type', $params);

		if($this->provider instanceof CSRFToken){
			$this::assertArrayHasKey('state', $params);
		}
	}

	public function testGetAuthURLRequestParams():void{
		$extraparams = ['response_type' => 'whatever', 'foo' => 'bar'];
		$scopes      = ['scope1', 'scope2', 'scope3'];

		$params = $this->invokeReflectionMethod('getAuthURLRequestParams', [$extraparams, $scopes]);

		$this::assertSame($this->options->key, $params['client_id']);
		$this::assertSame($this->options->callbackURL, $params['redirect_uri']);
		$this::assertSame('code', $params['response_type']);
		$this::assertSame('web_server', $params['type']);
		$this::assertSame(implode($this->provider::SCOPE_DELIMITER, $scopes), $params['scope']);
		$this::assertSame('bar', $params['foo']);
	}


	/*
	 * token response parser
	 */

	public function testParseTokenResponse():void{

		$response = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream($this::TEST_TOKEN))
		;

		/** @var \chillerlan\OAuth\Core\AccessToken $token */
		$token = $this->invokeReflectionMethod('parseTokenResponse', [$response]);

		$this::assertSame('2YotnFZFEjr1zCsicMWpAA', $token->accessToken);
		$this::assertSame('tGzv3JOkF0XG5Qx2TlKWIA', $token->refreshToken);
		$this::assertSame('example', $token->extraParams['token_type']);
		$this::assertSame('example_value', $token->extraParams['example_parameter']);
	}

	public function testParseTokenResponseWithScopes():void{
		$scopes = ['scope1', 'scope2', 'scope3'];

		$tokendata          = json_decode($this::TEST_TOKEN, true);
		$tokendata['scope'] = implode($this->provider::SCOPE_DELIMITER, $scopes);
		$tokenJSON          = json_encode($tokendata);

		$response = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream($tokenJSON))
		;

		/** @var \chillerlan\OAuth\Core\AccessToken $token */
		$token = $this->invokeReflectionMethod('parseTokenResponse', [$response]);

		$this::assertSame($scopes, $token->scopes);
	}

	public function testParseTokenResponseNoDataException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$response = $this->responseFactory->createResponse()->withBody($this->streamFactory->createStream('""'));

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}

	public function testParseTokenResponseErrorException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token');

		$response = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream('{"error":"whatever"}'))
		;

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}

	public function testParseTokenResponseNoTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('access token missing');

		$response = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream('{"foo":"bar"}'))
		;

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}


	/*
	 * access token
	 */

	public function testGetAccessTokenRequestBodyParams():void{
		$params = $this->invokeReflectionMethod('getAccessTokenRequestBodyParams', ['*test_code*']);

		$this::assertSame('*test_code*', $params['code']);
		$this::assertSame($this->options->key, $params['client_id']);
		$this::assertSame($this->options->secret, $params['client_secret']);
		$this::assertSame($this->options->callbackURL, $params['redirect_uri']);
		$this::assertSame('authorization_code', $params['grant_type']);
	}

	public function testSendAccessTokenRequest():void{
		$url      = 'https://localhost/access_token';
		$response = $this->invokeReflectionMethod('sendAccessTokenRequest', [$url, ['foo' => 'bar']]);
		$json     = MessageUtil::decodeJSON($response);

		$this::assertSame('identity', $json->headers->{'Accept-Encoding'});
		$this::assertSame('application/x-www-form-urlencoded', $json->headers->{'Content-Type'});
		$this::assertSame($url, $json->request->url);
		$this::assertSame('POST', $json->request->method);
		$this::assertSame('foo=bar', $json->body);
	}


	/*
	 * request authorization
	 */

	public function testGetRequestAuthorization():void{
		$request    = $this->requestFactory->createRequest('GET', 'https://foo.bar');
		$token      = new AccessToken(['accessTokenSecret' => 'test_token_secret', 'accessToken' => 'test_token']);
		$authMethod = $this->provider::AUTH_METHOD;

		// header (default)
		if($authMethod === OAuth2Interface::AUTH_METHOD_HEADER){
			$this::assertStringContainsString(
				$this->provider::AUTH_PREFIX_HEADER.' test_token',
				$this->provider->getRequestAuthorization($request, $token)->getHeaderLine('Authorization')
			);
		}
		// query
		elseif($authMethod === OAuth2Interface::AUTH_METHOD_QUERY){
			$this::assertStringContainsString(
				$this->provider::AUTH_PREFIX_QUERY.'=test_token',
				$this->provider->getRequestAuthorization($request, $token)->getUri()->getQuery()
			);
		}

	}


	/*
	 * client credentials
	 */

	public function testGetClientCredentialsTokenRequestBodyParams():void{

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('ClientCredentials N/A');
		}

		$scopes = ['scope1', 'scope2', 'scope3'];

		$params = $this->invokeReflectionMethod('getClientCredentialsTokenRequestBodyParams', [$scopes]);

		$this::assertSame('client_credentials', $params['grant_type']);
		$this::assertSame(implode($this->provider::SCOPE_DELIMITER, $scopes), $params['scope']);
	}

	public function testClientCredentialsTokenRequest():void{

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('ClientCredentials N/A');
		}

		$url      = 'https://localhost/access_token';
		$response = $this->invokeReflectionMethod('sendClientCredentialsTokenRequest', [$url, ['foo' => 'bar']]);
		$json     = MessageUtil::decodeJSON($response);

		$authHeader = 'Basic '.base64_encode($this->options->key.':'.$this->options->secret);

		$this::assertSame($authHeader, $json->headers->{'Authorization'});
		$this::assertSame('identity', $json->headers->{'Accept-Encoding'});
		$this::assertSame('application/x-www-form-urlencoded', $json->headers->{'Content-Type'});
		$this::assertSame($url, $json->request->url);
		$this::assertSame('POST', $json->request->method);
		$this::assertSame('foo=bar', $json->body);
	}

	public function testGetClientCredentialsNotSupportedException():void{

		if($this->provider instanceof ClientCredentials){
			$this->markTestSkipped('ClientCredentials supported');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('client credentials token not supported');

		$this->provider->getClientCredentialsToken();
	}

	/*
	 * CSRF state
	 */

	public function testSetCSRFState():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$params = $this->provider->setState(['foo' => 'bar']);

		$this::assertArrayHasKey('state', $params);
		$this::assertTrue($this->storage->hasCSRFState($this->provider->serviceName));
		$this::assertSame($params['state'], $this->storage->getCSRFState($this->provider->serviceName));
	}

	public function testCheckCSRFState():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->storage->storeCSRFState('test_state', $this->provider->serviceName);

		$this::assertTrue($this->storage->hasCSRFState($this->provider->serviceName));

		// will delete the state after a successful check
		$this->provider->checkState('test_state');

		$this::assertFalse($this->storage->hasCSRFState($this->provider->serviceName));
	}

	public function testCheckCSRFStateEmptyException():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid CSRF state');

		$this->provider->checkState();
	}

	public function testCheckCSRFStateInvalidStateException():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid CSRF state');

		$this->provider->checkState('invalid_test_state');
	}

	public function testCheckCSRFStateMismatchException():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->expectException(CSRFStateMismatchException::class);
		$this->expectExceptionMessage('CSRF state mismatch');

		$this->storage->storeCSRFState('known_state', $this->provider->serviceName);

		$this->provider->checkState('unknown_state');
	}

	public function testSetCSRFStateNotSupportedException(){

		if($this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken supported');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('CSRF protection not supported');

		$this->provider->setState([]);
	}

	public function testCheckCSRFStateNotSupportedException(){

		if($this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken supported');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('CSRF protection not supported');

		$this->provider->checkState('');
	}


	/*
	 * token refresh
	 */

	public function testGetRefreshAccessTokenRequestBodyParams():void{

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
		}

		$params = $this->invokeReflectionMethod('getRefreshAccessTokenRequestBodyParams', ['*refresh_token*']);

		$this::assertSame('*refresh_token*', $params['refresh_token']);
		$this::assertSame($this->options->key, $params['client_id']);
		$this::assertSame($this->options->secret, $params['client_secret']);
		$this::assertSame('web_server', $params['type']);
		$this::assertSame('refresh_token', $params['grant_type']);
	}

	public function testRefreshAccessTokenNoRefreshTokenException():void{

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
		}

		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage('no refresh token available, token expired [');

		$token = new AccessToken(['expires' => 1, 'refreshToken' => null]);
		$this->provider->storeAccessToken($token);

		$this->provider->refreshAccessToken();
	}

	public function testRefreshAccessTokenNotSupportedException():void{

		if($this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh supported');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('token refresh not supported');

		$this->provider->refreshAccessToken();
	}

}
