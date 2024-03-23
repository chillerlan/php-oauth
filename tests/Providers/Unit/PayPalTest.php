<?php
/**
 * Class PayPalTest
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\PayPal;
use function base64_encode;

/**
 * @property \chillerlan\OAuth\Providers\PayPal $provider
 */
final class PayPalTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return PayPal::class;
	}

	public function testGetAccessTokenRequestBodyParams():void{
		$params = $this->invokeReflectionMethod('getAccessTokenRequestBodyParams', ['*test_code*']);

		$this::assertSame('*test_code*', $params['code']);
		$this::assertSame($this->options->callbackURL, $params['redirect_uri']);
		$this::assertSame('authorization_code', $params['grant_type']);
	}

	public function testSendAccessTokenRequest():void{
		$url        = 'https://localhost/access_token';
		$response   = $this->invokeReflectionMethod('sendAccessTokenRequest', [$url, ['foo' => 'bar']]);
		$json       = MessageUtil::decodeJSON($response);

		// paypal uses basic auth instead of the credentials in the body
		$authHeader = 'Basic '.base64_encode($this->options->key.':'.$this->options->secret);

		$this::assertSame($authHeader, $json->headers->{'Authorization'});

		$this::assertSame('identity', $json->headers->{'Accept-Encoding'});
		$this::assertSame('application/x-www-form-urlencoded', $json->headers->{'Content-Type'});
		$this::assertSame($url, $json->request->url);
		$this::assertSame('POST', $json->request->method);
		$this::assertSame('foo=bar', $json->body);
	}

}
