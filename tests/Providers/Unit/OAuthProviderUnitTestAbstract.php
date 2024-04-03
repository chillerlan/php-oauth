<?php
/**
 * Class OAuthProviderUnitTestAbstract
 *
 * @created      18.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\Core\TokenInvalidate;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Storage\TokenNotFoundException;
use chillerlan\OAuthTest\Providers\ProviderUnitTestAbstract;
use PHPUnit\Framework\Attributes\DataProvider;

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

	public function testMagicGet():void{
		$this::assertSame($this->reflection->getShortName(), $this->provider->name);
		/** @noinspection PhpUndefinedFieldInspection */
		$this::assertNull($this->provider->foo);
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

		// creates a stream interface with the sting as content
		/** @var \Psr\Http\Message\StreamInterface $stream */
		$stream = $this->invokeReflectionMethod('setRequestBody', [$body, $request]);

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

		/** @var \Psr\Http\Message\StreamInterface $stream */
		$request = $this->invokeReflectionMethod('setRequestBody', [$body, $request]);

		$this::assertSame($expected, $request->getBody()->getContents());
	}


	public function testGetRequestBodyInvalidContentTypeForArrayException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid content-type for the given array body');

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
	 * token invalidate
	 */

	public function testTokenInvalidate():void{

		if(!$this->provider instanceof TokenInvalidate){
			$this::markTestSkipped('TokenInvalidate N/A');
		}

		$token = new AccessToken(['expires' => 42]);

		$this->provider->storeAccessToken($token);

		$this::assertTrue($this->storage->hasAccessToken($this->provider->name));
		$this::assertTrue($this->provider->invalidateAccessToken());
		$this::assertFalse($this->storage->hasAccessToken($this->provider->name));

		// token via param

		// the current token shouldn't be deleted
		$token2 = clone $token;
		$token2->accessToken = 'still here';

		$this->provider->storeAccessToken($token2);

		$this::assertTrue($this->provider->invalidateAccessToken($token));
		$this::assertSame('still here', $this->provider->getStorage()->getAccessToken($this->provider->name)->accessToken);
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

	public function testMeUnknownErrorException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('user info error HTTP/404');

		$response = $this->responseFactory
			->createResponse(404)
			->withHeader('Content-Type', 'application/json')
			->withBody($this->streamFactory->createStream('{}'))
		;

		$this->setMockResponse($response);

		$this->provider
			->storeAccessToken(new AccessToken(['expires' => 42]))
			->me()
		;
	}

}
