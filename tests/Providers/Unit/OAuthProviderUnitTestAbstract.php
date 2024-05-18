<?php
/**
 * Class OAuthProviderUnitTestAbstract
 *
 * @created      18.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @phan-file-suppress PhanUndeclaredMethod
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\InvalidAccessTokenException;
use chillerlan\OAuth\Core\OAuth2Interface;
use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\Core\TokenInvalidate;
use chillerlan\OAuth\Core\UnauthorizedAccessException;
use chillerlan\OAuth\Core\UserInfo;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Storage\TokenNotFoundException;
use chillerlan\OAuthTest\Providers\ProviderUnitTestAbstract;
use PHPUnit\Framework\Attributes\DataProvider;
use function sprintf;

/**
 * @property \chillerlan\OAuth\Core\OAuthInterface $provider
 */
abstract class OAuthProviderUnitTestAbstract extends ProviderUnitTestAbstract{

	/*
	 * common unit tests
	 */

	public function testOAuthInstance():void{
		$this::assertInstanceOf(OAuthInterface::class, $this->provider);
	}

	public function testProviderInstance():void{
		$this::assertInstanceOf($this->getProviderFQCN(), $this->provider);
	}

	public function testIdentifierIsNonEmpty():void{
		$this::assertNotEmpty($this->provider::IDENTIFIER);
	}

	/*
	 * request body
	 */

	public function testGetRequestBodyWithStreaminterface():void{
		$body    = $this->streamFactory->createStream('test');
		$request = $this->requestFactory->createRequest('GET', '');
		$request = $this->invokeReflectionMethod('setRequestBody', [$body, $request]);

		// simply returns the stream untouched
		$this::assertSame($body, $request->getBody());
	}

	public function testGetRequestBodyWithString():void{
		$body    = 'test';
		$request = $this->requestFactory->createRequest('GET', '');
		$request = $this->invokeReflectionMethod('setRequestBody', [$body, $request]);

		$this::assertSame($body, $request->getBody()->getContents());
	}

	public static function arrayBodyProvider():array{
		$body = ['test' => 'nope'];

		return [
			// urlencoded form fields
			[$body, 'application/x-www-form-urlencoded', 'test=nope'],
			// JSON
			[$body, 'application/json', '{"test":"nope"}'],
			[$body, 'application/vnd.api+json', '{"test":"nope"}'],
		];
	}

	#[DataProvider('arrayBodyProvider')]
	public function testGetRequestBodyWithArray(array $body, string $contentType, string $expected):void{

		$request = $this->requestFactory
			->createRequest('GET', '')
			->withHeader('Content-Type', $contentType)
		;

		$request = $this->invokeReflectionMethod('setRequestBody', [$body, $request]);

		$this::assertSame($expected, $request->getBody()->getContents());
	}


	public function testGetRequestBodyInvalidContentTypeForArrayException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid content-type "" for the given array body');

		$request = $this->requestFactory->createRequest('GET', '');

		$this->invokeReflectionMethod('setRequestBody', [['what'], $request]);
	}


	/*
	 * request target
	 */

	public static function requestTargetProvider():array{
		return [
			'empty'          => ['', 'https://example.com/api'],
			'slash'          => ['/', 'https://example.com/api/'],
			'no slashes'     => ['a', 'https://example.com/api/a'],
			'leading slash'  => ['/b', 'https://example.com/api/b'],
			'trailing slash' => ['c/', 'https://example.com/api/c/'],
			'full url given' => ['https://example.com/other/path/d', 'https://example.com/other/path/d'],
			'ignore params'  => ['https://example.com/api/e/?with=param#foo', 'https://example.com/api/e/'],
			'subdomain'      => ['https://api.sub.example.com/a/b/c', 'https://api.sub.example.com/a/b/c'],
			'enforce https'  => ['wtf://example.com/a/b/c', 'https://example.com/a/b/c'],
		];
	}

	#[DataProvider('requestTargetProvider')]
	public function testGetRequestTarget(string $path, string $expected):void{
		$this->setReflectionProperty('apiURL', 'https://example.com/api/');

		$this::assertSame($expected, $this->invokeReflectionMethod('getRequestTarget', [$path]));
	}

	public function testGetRequestTargetProviderMismatchException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('given host (nope.com) does not match provider');

		$this->invokeReflectionMethod('getRequestTarget', ['https://nope.com/ahrg']);
	}


	/*
	 * request authorization
	 */

	public function testGetRequestAuthorizationInvalidTokenException():void{
		$this->expectException(InvalidAccessTokenException::class);

		$this->options->tokenAutoRefresh = false;

		$request = $this->requestFactory->createRequest('GET', 'https://example.com');

		$this->provider->getRequestAuthorization($request, $this->getTestToken([]));
	}


	/*
	 * request
	 */

	public function testRequest():void{

		$response = $this->provider
			->storeAccessToken($this->getTestToken())
			->request(
				path           : '/',
				method         : 'post',
				// coverage
				body           : ['foo' => 'bar'],
				headers        : ['Content-Type' => 'application/json'],
				protocolVersion: '1.1',
			)
		;

		$data = MessageUtil::decodeJSON($response, true);

		$this::assertSame('{"foo":"bar"}', $data['body']);

		if($this->provider instanceof OAuth2Interface && $this->provider::AUTH_METHOD === OAuth2Interface::AUTH_METHOD_QUERY){
			$this::assertArrayHasKey($this->provider::AUTH_PREFIX_QUERY, $data['request']['params']);
		}
		else{
			$this::assertArrayHasKey('Authorization', $data['headers']);
		}
	}

	public function testRequestUnauthorizedException():void{
		$this->expectException(UnauthorizedAccessException::class);

		$this->setMockResponse($this->responseFactory->createResponse(401));

		$this->provider->storeAccessToken($this->getTestToken())->request('/');
	}

	/*
	 * token invalidate
	 */

	public function testTokenInvalidate():void{

		if(!$this->provider instanceof TokenInvalidate){
			$this::markTestSkipped('TokenInvalidate N/A');
		}

		$token = $this->getTestToken();

		$this->provider->storeAccessToken($this->getTestToken());

		$this::assertTrue($this->storage->hasAccessToken($this->provider->getName()));
		$this::assertTrue($this->provider->invalidateAccessToken());
		$this::assertFalse($this->storage->hasAccessToken($this->provider->getName()));

		// token via param

		// the current token shouldn't be deleted
		$token2 = clone $token;
		$token2->accessToken = 'still here';

		$this->provider->storeAccessToken($token2);

		$this::assertTrue($this->provider->invalidateAccessToken($token));
		$this::assertSame('still here', $this->provider->getStorage()->getAccessToken($this->provider->getName())->accessToken);
	}

	public function testTokenInvalidateNoTokenException():void{

		if(!$this->provider instanceof TokenInvalidate){
			$this::markTestSkipped('TokenInvalidate N/A');
		}

		$this->expectException(TokenNotFoundException::class);

		$this->provider->invalidateAccessToken();
	}


	/*
	 * authenticated user me()
	 */

	public function testGetMeResponseData():void{

		if(!$this->provider instanceof UserInfo){
			$this::markTestSkipped('UserInfo N/A');
		}

		$response = $this->responseFactory
			->createResponse()
			->withHeader('Content-Type', 'application/json')
			->withBody($this->streamFactory->createStream('{"foo":"bar"}'))
		;

		$this->setMockResponse($response);

		$this->provider->storeAccessToken($this->getTestToken());

		// we're going to call the internal response parser here instead of me(),
		// because it would be messy to verify the resonse
		$data = $this->invokeReflectionMethod('getMeResponseData', ['/me']);

		$this::assertSame('bar', $data['foo']);
	}

	public function testMeResponseInvalidContentTypeException():void{

		if(!$this->provider instanceof UserInfo){
			$this::markTestSkipped('UserInfo N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid content type "foo/bar", expected JSON');

		$response = $this->responseFactory
			->createResponse()
			->withHeader('Content-Type', 'foo/bar')
			->withBody($this->streamFactory->createStream('{}'))
		;

		$this->setMockResponse($response);

		$this->provider->storeAccessToken($this->getTestToken())->me();
	}

	public function testMeUnknownErrorException():void{

		if(!$this->provider instanceof UserInfo){
			$this::markTestSkipped('UserInfo N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('user info error HTTP/404');

		$response = $this->responseFactory
			->createResponse(404)
			->withHeader('Content-Type', 'application/json')
			->withBody($this->streamFactory->createStream('{}'))
		;

		$this->setMockResponse($response);

		$this->provider->storeAccessToken($this->getTestToken())->me();
	}

	public function testHandleMeResponseErrorUnauthorizedException():void{
		$this->expectException(UnauthorizedAccessException::class);

		$response = $this->responseFactory->createResponse(401);

		$this->invokeReflectionMethod('handleMeResponseError', [$response]);
	}

	public function testHandleMeResponseErrorNoJSONContentTypeException():void{
		$message = 'oh noes';

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage($message);

		$body     = $this->streamFactory->createStream($message);
		$response = $this->responseFactory
			->createResponse(420)
			->withHeader('Content-Type', 'text/plain')
			->withBody($body)
		;

		$this->invokeReflectionMethod('handleMeResponseError', [$response]);
	}

	public static function jsonErrorProvider():array{
		$message = 'oh noes';

		return [
			'string' => [sprintf('{"error":"%s"}', $message), $message],
			'array'  => [sprintf('{"error":{"message":"%s"}}', $message), $message],
		];
	}

	#[DataProvider('jsonErrorProvider')]
	public function testHandleMeResponseErrorWithJSONBodyException(string $json, string $expected):void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage($expected);

		$body     = $this->streamFactory->createStream($json);
		$response = $this->responseFactory
			->createResponse(420)
			->withHeader('Content-Type', 'application/json')
			->withBody($body)
		;

		$this->invokeReflectionMethod('handleMeResponseError', [$response]);
	}

}
