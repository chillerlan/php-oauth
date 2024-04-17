<?php
/**
 * Class LastFMTest
 *
 * @created      05.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\{LastFM, ProviderException};
use InvalidArgumentException;

/**
 * @property \chillerlan\OAuth\Providers\LastFM $provider
 */
final class LastFMTest extends OAuthProviderUnitTestAbstract{

	protected const TEST_TOKEN = '{"session":{"name":"lfm-user","key":"sk","subscriber":0}}';

	protected const SCROBBLE_RESPONSE_SINGLE =
		'{"scrobbles":{"scrobble":{"artist":{"corrected":"0","#text":"Helium"},"album":{"corrected":"0"},'.
		'"track":{"corrected":"0","#text":"Vibrations"},"ignoredMessage":{"code":"0","#text":""},'.
		'"albumArtist":{"corrected":"0","#text":""},"timestamp":"1712992925"},"@attr":{"ignored":0,"accepted":1}}}';

	protected const SCROBBLE_RESPONSE_MULTI =
		'{"scrobbles":{"scrobble":[{"artist":{"corrected":"0","#text":"Helium"},"album":{"corrected":"0"},'.
		'"track":{"corrected":"0","#text":"Vibrations"},"ignoredMessage":{"code":"0","#text":""},'.
		'"albumArtist":{"corrected":"0","#text":""},"timestamp":"1712992925"},{"artist":{"corrected":"0","#text":"Helium"},'.
		'"album":{"corrected":"0"},"track":{"corrected":"0","#text":"Leon\'s Space Song"},"ignoredMessage":'.
		'{"code":"0","#text":""},"albumArtist":{"corrected":"0","#text":""},"timestamp":"1712993092"}],'.
		'"@attr":{"ignored":0,"accepted":2}}}';



	protected function getProviderFQCN():string{
		return LastFM::class;
	}

	/*
	 * auth URL
	 */

	public function testGetAuthURL():void{
		$uri = $this->provider->getAuthorizationURL();

		$this::assertSame('api_key=testclient', $uri->getQuery());
	}


	/*
	 * token response parser
	 */

	public function testParseAccessTokenResponse():void{
		$body     = $this->streamFactory->createStream($this::TEST_TOKEN);
		$response = $this->responseFactory->createResponse()->withBody($body);

		/** @var \chillerlan\OAuth\Core\AccessToken $token */
		$token = $this->invokeReflectionMethod('parseTokenResponse', [$response]);

		$this::assertSame('sk', $token->accessToken);
	}

	public function testParseTokenResponseNoDataException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$this->invokeReflectionMethod('parseTokenResponse', [$this->responseFactory->createResponse()]);
	}

	public function testParseTokenResponseErrorException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token: "whatever"');

		$body     = $this->streamFactory->createStream('{"error":"666","message":"whatever"}');
		$response = $this->responseFactory->createResponse()->withBody($body);

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}

	public function testParseTokenResponseNoTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('token missing');

		$body     = $this->streamFactory->createStream('{"session":{"name":"lfm-user","subscriber":0}}');
		$response = $this->responseFactory->createResponse()->withBody($body);

		$this->invokeReflectionMethod('parseTokenResponse', [$response]);
	}


	/*
	 * access token
	 */

	public function testGetAccessToken():void{
		$this->setMockResponse($this->streamFactory->createStream($this::TEST_TOKEN));

		$token = $this->provider->getAccessToken('code');

		$this->assertSame('sk', $token->accessToken);
		$this::assertSame('lfm-user', $token->extraParams['session']['name']);
	}

	public function testGetAccessTokenRequestBodyParams():void{
		$params = $this->invokeReflectionMethod('getAccessTokenRequestBodyParams', ['*test_code*']);

		$this::assertSame('*test_code*', $params['token']);
		$this::assertSame('auth.getSession', $params['method']);
		$this::assertSame('json', $params['format']);
		$this::assertSame($this->options->key, $params['api_key']);
		$this::assertArrayHasKey('api_sig', $params);
	}


	/*
	 * request
	 */

	public function testRequest():void{

		$response = $this->provider
			->storeAccessToken($this->getTestToken())
			->request(
				path           : 'foo.bar',
				method         : 'post',
				// coverage
				body           : ['foo' => 'bar'],
				headers        : ['Content-Type' => 'application/json'],
				protocolVersion: '1.1',
			)
		;

		$data = MessageUtil::decodeJSON($response, true);

		$this::assertStringContainsString('api_key', $data['body']);
		$this::assertStringContainsString('api_sig', $data['body']);
		$this::assertStringContainsString('method', $data['body']);
		$this::assertStringContainsString('sk', $data['body']);
	}

	public function testRequestBodyMustBeArrayException():void{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('$body must be an array');

		$this->provider->request(path: '/', body: 'boo');
	}

	public function testGetRequestAuthorizationInvalidTokenException():void{
		$this::markTestSkipped('N/A');
	}

	public function testMeResponseInvalidContentTypeException():void{
		$this::markTestSkipped('N/A');
	}


	/*
	 * scrobble
	 */

	public function testScrobble():void{
		$body = $this->streamFactory->createStream($this::SCROBBLE_RESPONSE_MULTI);

		$this->setMockResponse($body);

		$scrobbles = [
			['artist' => 'Helium', 'track' => 'Vibrations', 'timestamp' => 1712992925],
			['artist' => 'Helium', 'track' => 'Leon\'s Space Song', 'timestamp' => 1712993092],
		];

		$response = $this->provider->storeAccessToken($this->getTestToken())->scrobble($scrobbles);

		$this::assertCount(2, $response[0]['scrobble']);
		$this::assertSame(2, $response[0]['@attr']['accepted']);
	}

	public function testScrobbleSingleTrack():void{
		$body = $this->streamFactory->createStream($this::SCROBBLE_RESPONSE_SINGLE);

		$this->setMockResponse($body);

		$scrobbles = ['artist' => 'Helium', 'track' => 'Vibrations', 'timestamp' => 1712992925];

		$response = $this->provider->storeAccessToken($this->getTestToken())->scrobble($scrobbles);

		$this::assertArrayHasKey('artist', $response[0]['scrobble']);
		$this::assertSame(1, $response[0]['@attr']['accepted']);
	}

}
