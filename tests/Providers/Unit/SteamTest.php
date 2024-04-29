<?php
/**
 * Class SteamTest
 *
 * @created      15.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Providers\Steam;
use chillerlan\OAuthTest\Attributes\Provider;
use function rawurlencode, sprintf;

/**
 * @property \chillerlan\OAuth\Providers\Steam $provider
 */
#[Provider(Steam::class)]
final class SteamTest extends OAuthProviderUnitTestAbstract{

	protected const ID_VALID   = "ns:http://specs.openid.net/auth/2.0\x0ais_valid:true\x0a";
	protected const ID_INVALID = "ns:http://specs.openid.net/auth/2.0\x0ais_valid:false\x0a";

	// array from $_GET during the callback
	protected const OPENID_CALLBACK = [
		'openid_ns'             => 'http://specs.openid.net/auth/2.0',
		'openid_mode'           => 'id_res',
		'openid_op_endpoint'    => 'https://steamcommunity.com/openid/login',
		'openid_claimed_id'     => 'https://steamcommunity.com/openid/id/69420',
		'openid_identity'       => 'https://steamcommunity.com/openid/id/69420',
		'openid_return_to'      => 'https://smiley.codes/oauth/',
		'openid_response_nonce' => '2021-03-16T06:40:46ZtLLZ4JqhLZ2IULBg8x2P8YitHQY=',
		'openid_assoc_handle'   => '1234567890',
		'openid_signed'         => 'signed,op_endpoint,claimed_id,identity,return_to,response_nonce,assoc_handle',
		'openid_sig'            => '7WEtj64YlaJLNqL6M0gZvVmOLFg=',
	];


	/*
	 * auth URL
	 */

	public function testGetAuthURL():void{
		$query = $this->provider->getAuthorizationURL()->getQuery();

		$this::assertStringContainsString('openid.mode=checkid_setup', $query);
		$this::assertStringContainsString(sprintf('openid.return_to=%s', rawurlencode($this->options->callbackURL)), $query);
		$this::assertStringContainsString(sprintf('openid.realm=%s', rawurlencode($this->options->key)), $query);
	}


	/*
	 * token response parser
	 */

	public function testParseAccessTokenResponse():void{
		$body     = $this->streamFactory->createStream($this::ID_VALID);
		$response = $this->responseFactory->createResponse()->withBody($body);

		/** @var \chillerlan\OAuth\Core\AccessToken $token */
		$token = $this->invokeReflectionMethod('parseTokenResponse', [$response, 'https://steamcommunity.com/openid/id/69420']);

		$this::assertSame('69420', $token->accessToken);
	}

	public function testParseTokenResponseNoDataException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$this->invokeReflectionMethod('parseTokenResponse', [$this->responseFactory->createResponse(), 'nope']);
	}

	public function testParseTokenResponseInvalidIdException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid id');

		$body     = $this->streamFactory->createStream($this::ID_INVALID);
		$response = $this->responseFactory->createResponse()->withBody($body);

		$this->invokeReflectionMethod('parseTokenResponse', [$response, 'nope']);
	}


	/*
	 * access token
	 */

	public function testGetAccessToken():void{
		$this->setMockResponse($this->streamFactory->createStream($this::ID_VALID));

		$token = $this->provider->getAccessToken($this::OPENID_CALLBACK);

		$this->assertSame('69420', $token->accessToken);
	}

	public function testGetAccessTokenRequestBodyParams():void{
		$params = $this->invokeReflectionMethod('getAccessTokenRequestBodyParams', [$this::OPENID_CALLBACK]);

		$this::assertArrayHasKey('openid.sig', $params);
		$this::assertSame('check_authentication', $params['openid.mode']);
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
		$this::assertArrayHasKey('key', $data['request']['params']);
	}

	public function testGetRequestAuthorizationInvalidTokenException():void{
		$this::markTestSkipped('N/A');
	}

}
