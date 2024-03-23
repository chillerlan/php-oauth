<?php
/**
 * Class OAuth1ProviderUnitTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\{AccessToken, OAuth1Interface};
use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\ProviderException;
use function str_starts_with;

/**
 * @property \chillerlan\OAuth\Core\OAuth1Interface $provider
 */
abstract class OAuth1ProviderUnitTestAbstract extends OAuthProviderUnitTestAbstract{

	public function testOAuth1Instance():void{
		$this::assertInstanceOf(OAuth1Interface::class, $this->provider);
	}

	/*
	 * auth URL
	 */

	public function testGetRequestTokenRequestParams():void{
		$params = $this->invokeReflectionMethod('getRequestTokenRequestParams');

		$this::assertSame($this->options->callbackURL, $params['oauth_callback']);
		$this::assertSame($this->options->key, $params['oauth_consumer_key']);
		$this::assertArrayHasKey('oauth_nonce', $params);
		$this::assertSame('HMAC-SHA1', $params['oauth_signature_method']);
		$this::assertArrayHasKey('oauth_timestamp', $params);
		$this::assertSame('1.0', $params['oauth_version']);
	}

	public function testSendRequestTokenRequest():void{
		$url      = 'https://localhost/request_token';
		$response = $this->invokeReflectionMethod('sendRequestTokenRequest', [$url]);
		$json     = MessageUtil::decodeJSON($response);

		$this::assertTrue(str_starts_with($json->headers->{'Authorization'}, 'OAuth '));
		$this::assertSame('identity', $json->headers->{'Accept-Encoding'});
		$this::assertSame('0', $json->headers->{'Content-Length'});
		$this::assertSame('POST', $json->request->method);
	}


	/*
	 * token response parser
	 */

	public function testParseTokenResponse():void{
		// from https://datatracker.ietf.org/doc/html/rfc5849#section-2.3
		$responseBody = 'oauth_token=j49ddk933skd9dks&oauth_token_secret=ll399dj47dskfjdk';

		$response = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream($responseBody))
		;

		/** @var \chillerlan\OAuth\Core\AccessToken $token */
		$token = $this->invokeReflectionMethod('parseTokenResponse', [$response]);

		$this::assertSame('j49ddk933skd9dks', $token->accessToken);
		$this::assertSame('ll399dj47dskfjdk', $token->accessTokenSecret);
	}

	public function testParseTemporaryCredentialsTokenResponse():void{
		// from https://datatracker.ietf.org/doc/html/rfc5849#section-2.1
		$responseBody = 'oauth_token=hdk48Djdsa&oauth_token_secret=xyz4992k83j47x0b&oauth_callback_confirmed=true';

		$response = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream($responseBody))
		;

		/** @var \chillerlan\OAuth\Core\AccessToken $token */
		$token = $this->invokeReflectionMethod('parseTokenResponse', [$response, true]);

		$this::assertSame('hdk48Djdsa', $token->accessToken);
		$this::assertSame('xyz4992k83j47x0b', $token->accessTokenSecret);
	}

	public function testParseTokenResponseNoDataException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$response = $this->responseFactory->createResponse();

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}

	public function testParseTokenResponseErrorException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token: "whatever"');

		$body     = $this->streamFactory->createStream('error=whatever');
		$response = $this->responseFactory->createResponse()->withBody($body);

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}

	public function testParseTokenResponseNoTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid token');

		$body     = $this->streamFactory->createStream('oauth_token=whatever');
		$response = $this->responseFactory->createResponse()->withBody($body);

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}

	public function testParseTokenResponseConfirmCallbackException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('oauth callback unconfirmed');

		$body     = $this->streamFactory->createStream('oauth_token=whatever&oauth_token_secret=whatever_secret');
		$response = $this->responseFactory->createResponse()->withBody($body);

		$this->invokeReflectionMethod('parseTokenResponse', [$response, true]);
	}


	/*
	 * access token
	 */

	public function testSendAccessTokenRequest():void{
		// we need the request token for the access token request
		$requestToken = new AccessToken(['accessToken' => 'hdk48Djdsa', 'accessTokenSecret' => 'xyz4992k83j47x0b']);
		$this->provider->storeAccessToken($requestToken);

		$response = $this->invokeReflectionMethod('sendAccessTokenRequest', ['*verifier*']);
		$json     = MessageUtil::decodeJSON($response);

		// check if the verifier is set
		$this::assertSame('*verifier*', $json->request->params->{'oauth_verifier'});

		$this::assertTrue(str_starts_with($json->headers->{'Authorization'}, 'OAuth '));
		$this::assertSame('identity', $json->headers->{'Accept-Encoding'});
		$this::assertSame('0', $json->headers->{'Content-Length'});
		$this::assertSame('POST', $json->request->method);
	}


	/*
	 * request authorization
	 */

	public function testGetRequestAuthorization():void{
		$request = $this->requestFactory->createRequest('GET', 'https://foo.bar');
		$token   = new AccessToken(['accessTokenSecret' => 'test_token_secret', 'accessToken' => 'test_token']);

		$authHeader = $this->provider
			->getRequestAuthorization($request, $token)
			->getHeaderLine('Authorization')
		;

		$this::assertStringContainsString('OAuth oauth_consumer_key="'.$this->options->key.'"', $authHeader);
		$this::assertStringContainsString('oauth_token="test_token"', $authHeader);
	}


	/*
	 * signature
	 */

	public function testGetSignature():void{
		$expected = 'fvkt6r6LhR0TgMvDOGsSlzB7IR4=';

		$signature = $this->invokeReflectionMethod(
			'getSignature',
			['https://localhost/api/whatever', ['foo' => 'bar', 'oauth_signature' => 'should not see me!'], 'GET'],
		);

		$this::assertSame($expected, $signature);

		// the "oauth_signature" parameter should be unset if present
		$signature = $this->invokeReflectionMethod(
			'getSignature',
			['https://localhost/api/whatever', ['foo' => 'bar'], 'GET'],
		);

		$this::assertSame($expected, $signature);
	}

	public function testGetSignatureInvalidURLException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('getSignature: invalid url');

		$this->invokeReflectionMethod('getSignature', ['http://localhost/boo', [], 'GET']);
	}


}
