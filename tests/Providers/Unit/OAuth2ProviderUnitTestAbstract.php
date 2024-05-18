<?php
/**
 * Class OAuth2ProviderUnitTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @phan-file-suppress PhanUndeclaredMethod
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\{
	ClientCredentials, CSRFStateMismatchException, CSRFToken, OAuth2Interface,
	PAR, PKCE, TokenRefresh, UnauthorizedAccessException
};
use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\OAuthException;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Storage\StateNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use function base64_encode, implode, json_decode, json_encode;

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
abstract class OAuth2ProviderUnitTestAbstract extends OAuthProviderUnitTestAbstract{

	// from https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.4
	protected const TEST_TOKEN = '{"access_token":"2YotnFZFEjr1zCsicMWpAA","token_type":"example","expires_in":3600,'.
	                             '"refresh_token":"tGzv3JOkF0XG5Qx2TlKWIA","example_parameter":"example_value"}';


	/*
	 * common unit tests
	 */

	public function testOAuth2Instance():void{
		$this::assertInstanceOf(OAuth2Interface::class, $this->provider);
	}


	/*
	 * auth URL
	 */

	public function testGetAuthURL():void{

		if($this->provider instanceof PAR){
			$this::markTestSkipped('PAR supported');
		}

		$uri    = $this->provider->getAuthorizationURL();
		$params = QueryUtil::parse($uri->getQuery());

		$this::assertSame($this->getReflectionProperty('authorizationURL'), (string)$uri->withQuery(''));

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

		$params = $this->invokeReflectionMethod('getAuthorizationURLRequestParams', [$extraparams, $scopes]);

		$this::assertSame($this->options->key, $params['client_id']);
		$this::assertSame($this->options->callbackURL, $params['redirect_uri']);
		$this::assertSame('code', $params['response_type']);
		$this::assertSame('web_server', $params['type']);
		$this::assertSame(implode($this->provider::SCOPES_DELIMITER, $scopes), $params['scope']);
		$this::assertSame('bar', $params['foo']);
	}

	public function testGetParAuthURL():void{

		if(!$this->provider instanceof PAR){
			$this::markTestSkipped('PAR N/A');
		}

		// @link https://datatracker.ietf.org/doc/html/rfc9126#name-successful-response
		$json = '{"request_uri":"urn:ietf:params:oauth:request_uri:6esc_11ACC5bwc014ltc14eY22c","expires_in":60}';

		$this->setMockResponse($this->streamFactory->createStream($json));

		$uri    = $this->provider->getAuthorizationURL();
		$params = QueryUtil::parse($uri->getQuery());

		$this::assertSame($this->options->key, $params['client_id']);
		$this::assertSame('urn:ietf:params:oauth:request_uri:6esc_11ACC5bwc014ltc14eY22c', $params['request_uri']);
	}

	public function testGetParAuthURLErrorException():void{

		if(!$this->provider instanceof PAR){
			$this::markTestSkipped('PAR N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid_request');

		// @link https://datatracker.ietf.org/doc/html/rfc9126#name-error-response
		$json = '{"error":"invalid_request","error_description":"The redirect_uri is not valid for the given client"}';

		$response = $this->responseFactory
			->createResponse(400)
			->withBody($this->streamFactory->createStream($json))
		;

		$this->setMockResponse($response);

		$this->provider->getAuthorizationURL();
	}


	/*
	 * token response parser
	 */

	public function testParseTokenResponse():void{
		$body     = $this->streamFactory->createStream($this::TEST_TOKEN);
		$response = $this->responseFactory->createResponse()->withBody($body);

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
		$tokendata['scope'] = implode($this->provider::SCOPES_DELIMITER, $scopes);
		$tokenJSON          = json_encode($tokendata);

		$body     = $this->streamFactory->createStream($tokenJSON);
		$response = $this->responseFactory->createResponse()->withBody($body);

		/** @var \chillerlan\OAuth\Core\AccessToken $token */
		$token = $this->invokeReflectionMethod('parseTokenResponse', [$response]);

		$this::assertSame($scopes, $token->scopes);
	}

	public function testParseTokenResponseNoDataException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$body     = $this->streamFactory->createStream('""');
		$response = $this->responseFactory->createResponse()->withBody($body);

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}

	public function testParseTokenResponseErrorException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token');

		$body     = $this->streamFactory->createStream('{"error":"whatever"}');
		$response = $this->responseFactory->createResponse()->withBody($body);

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}

	public function testParseTokenResponseUnauthorizedException():void{
		$this->expectException(UnauthorizedAccessException::class);
		$this->expectExceptionMessage('Unauthorized');

		$body     = $this->streamFactory->createStream('{"error":"Unauthorized"}');
		$response = $this->responseFactory->createResponse(401)->withBody($body);

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}

	public function testParseTokenResponseNoTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('access token missing');

		$body     = $this->streamFactory->createStream('{"foo":"bar"}');
		$response = $this->responseFactory->createResponse()->withBody($body);

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}


	/*
	 * access token
	 */

	public function testGetAccessToken():void{
		$this->setMockResponse($this->streamFactory->createStream($this::TEST_TOKEN));

		$this->storage->storeCSRFState('mock_test_state', $this->provider->name);

		if($this->provider instanceof PKCE){
			// store a PKCE verifier that is used in this test
			$verifier = $this->provider->generateVerifier($this->options->pkceVerifierLength);

			$this->storage->storeCodeVerifier($verifier, $this->provider->name);

			$this::assertTrue($this->storage->hasCodeVerifier($this->provider->name));
		}

		$token = $this->provider->getAccessToken('code', 'mock_test_state');

		$this->assertSame('2YotnFZFEjr1zCsicMWpAA', $token->accessToken);
		$this::assertSame('example_value', $token->extraParams['example_parameter']);

		if($this->provider instanceof PKCE){
			// the verifier should have been deleted in the process
			$this::assertFalse($this->storage->hasCodeVerifier($this->provider->name));
		}
	}

	public function testGetAccessTokenRequestBodyParams():void{
		$verifier = $this->provider->generateVerifier($this->options->pkceVerifierLength);

		$this->storage->storeCodeVerifier($verifier, $this->provider->name);

		$params = $this->invokeReflectionMethod('getAccessTokenRequestBodyParams', ['*test_code*']);

		$this::assertSame('*test_code*', $params['code']);
		$this::assertSame($this->options->callbackURL, $params['redirect_uri']);
		$this::assertSame('authorization_code', $params['grant_type']);

		if(!$this->provider::USES_BASIC_AUTH_IN_ACCESS_TOKEN_REQUEST){
			$this::assertSame($this->options->key, $params['client_id']);
			$this::assertSame($this->options->secret, $params['client_secret']);
		}

		if($this->provider instanceof PKCE){
			$this::assertSame($verifier, $params['code_verifier']);
		}

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

		if($this->provider::USES_BASIC_AUTH_IN_ACCESS_TOKEN_REQUEST){
			$authHeader = 'Basic '.base64_encode($this->options->key.':'.$this->options->secret);

			$this::assertSame($authHeader, $json->headers->{'Authorization'});
		}

	}


	/*
	 * request authorization
	 */

	public function testGetRequestAuthorization():void{
		$this->provider->storeAccessToken($this->getTestToken());

		$request = $this->requestFactory->createRequest('GET', 'https://foo.bar');

		// header (default)
		if($this->provider::AUTH_METHOD === OAuth2Interface::AUTH_METHOD_HEADER){
			$this::assertStringContainsString(
				$this->provider::AUTH_PREFIX_HEADER.' test_access_token',
				$this->provider->getRequestAuthorization($request)->getHeaderLine('Authorization')
			);
		}
		// query
		elseif($this->provider::AUTH_METHOD === OAuth2Interface::AUTH_METHOD_QUERY){
			$this::assertStringContainsString(
				$this->provider::AUTH_PREFIX_QUERY.'=test_access_token',
				$this->provider->getRequestAuthorization($request)->getUri()->getQuery()
			);
		}

	}

	public function testGetRequestAuthorizationWithTokenRefresh():void{

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
		}

		$token = $this->getTestToken([
			'accessToken'  => 'test_token',
			'refreshToken' => 'test_refresh_token',
			// expiry unknown
		]);

		$this->storage->storeAccessToken($token, $this->provider->name);
		$this->setMockResponse($this->streamFactory->createStream($this::TEST_TOKEN));

		$request = $this->requestFactory->createRequest('GET', 'https://foo.bar');
		$this->provider->getRequestAuthorization($request);

		// token was refreshed
		$token = $this->storage->getAccessToken($this->provider->name);
		$this->assertSame('2YotnFZFEjr1zCsicMWpAA', $token->accessToken);
	}


	/*
	 * client credentials
	 */

	public function testGetClientCredentialsToken():void{

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('ClientCredentials N/A');
		}

		$this->setMockResponse($this->streamFactory->createStream($this::TEST_TOKEN));

		$token = $this->provider->getClientCredentialsToken();

		$this->assertSame('2YotnFZFEjr1zCsicMWpAA', $token->accessToken);
	}

	public function testGetClientCredentialsTokenRequestBodyParams():void{

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('ClientCredentials N/A');
		}

		$scopes = ['scope1', 'scope2', 'scope3'];

		$params = $this->invokeReflectionMethod('getClientCredentialsTokenRequestBodyParams', [$scopes]);

		$this::assertSame('client_credentials', $params['grant_type']);
		$this::assertSame(implode($this->provider::SCOPES_DELIMITER, $scopes), $params['scope']);
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
		$this::assertTrue($this->storage->hasCSRFState($this->provider->name));
		$this::assertSame($params['state'], $this->storage->getCSRFState($this->provider->name));
	}

	public function testCheckCSRFState():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->storage->storeCSRFState('test_state', $this->provider->name);

		$this::assertTrue($this->storage->hasCSRFState($this->provider->name));

		// will delete the state after a successful check
		$this->provider->checkState('test_state');

		$this::assertFalse($this->storage->hasCSRFState($this->provider->name));
	}

	public function testCheckCSRFStateEmptyException():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid CSRF state');

		$this->provider->checkState();
	}

	public function testCheckCSRFStateNotFoundException():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->expectException(StateNotFoundException::class);

		$this->provider->checkState('invalid_test_state');
	}

	public function testCheckCSRFStateMismatchException():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->expectException(CSRFStateMismatchException::class);
		$this->expectExceptionMessage('CSRF state mismatch');

		$this->storage->storeCSRFState('known_state', $this->provider->name);

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

	public function testRefreshAccessToken():void{

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
		}

		// delete the refresh token from the response for some coverage (:
		$tokenResponse = json_decode($this::TEST_TOKEN);
		$tokenResponse->refresh_token = '';

		$this->setMockResponse($this->streamFactory->createStream(json_encode($tokenResponse)));

		$oldToken = $this->getTestToken();
		$newToken = $this->provider->refreshAccessToken($oldToken);

		$this->assertSame('2YotnFZFEjr1zCsicMWpAA', $newToken->accessToken);
	}

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
		$this->expectExceptionMessage('no refresh token available, token expired');

		$token = $this->getTestToken(['expires' => 1, 'refreshToken' => null]);

		$this->provider
			->storeAccessToken($token)
			->refreshAccessToken()
		;
	}

	public function testRefreshAccessTokenNotSupportedException():void{

		if($this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh supported');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('token refresh not supported');

		$this->provider->refreshAccessToken();
	}


	/*
	 * PKCE
	 */

	/**
	 * test values from RFC-7636, Appendix B
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc7636#appendix-B
	 */
	public static function challengeProvider():array{
		$verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';

		return [
			'plain' => [PKCE::CHALLENGE_METHOD_PLAIN, $verifier, $verifier],
			'S256'  => [PKCE::CHALLENGE_METHOD_S256, $verifier, 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM'],
		];
	}

	#[DataProvider('challengeProvider')]
	public function testGenerateChallenge(string $challengeMethod, string $verifier, string $expected):void{

		if(!$this->provider instanceof PKCE){
			$this->markTestSkipped('PKCE N/A');
		}

		$this::assertSame($expected, $this->provider->generateChallenge($verifier, $challengeMethod));
	}

	public function testSetCodeChallengeInvalidParams():void{

		if(!$this->provider instanceof PKCE){
			$this->markTestSkipped('PKCE N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid authorization request params');

		$this->provider->setCodeChallenge(['param' => 'value'], PKCE::CHALLENGE_METHOD_S256);
	}

	public function testSetCodeVerifierInvalidParams():void{

		if(!$this->provider instanceof PKCE){
			$this->markTestSkipped('PKCE N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid authorization request body');

		$this->provider->setCodeVerifier(['param' => 'value']);
	}

	public function testSetCodeChallengeNotSupportedException():void{

		if($this->provider instanceof PKCE){
			$this->markTestSkipped('PKCE supported');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('PKCE challenge not supported');

		$this->provider->setCodeChallenge([], PKCE::CHALLENGE_METHOD_S256);
	}

	public function testSetCodeVerifierNotSupportedException():void{

		if($this->provider instanceof PKCE){
			$this->markTestSkipped('PKCE supported');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('PKCE challenge not supported');

		$this->provider->setCodeVerifier([]);
	}

}
