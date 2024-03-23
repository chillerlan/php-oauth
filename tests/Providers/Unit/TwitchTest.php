<?php
/**
 * Class TwitchTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Twitch;
use function implode;

/**
 * @property \chillerlan\OAuth\Providers\Twitch $provider
 */
class TwitchTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Twitch::class;
	}

	public function testGetClientCredentialsTokenRequestBodyParams():void{
		$scopes = ['scope1', 'scope2', 'scope3'];

		$params = $this->invokeReflectionMethod('getClientCredentialsTokenRequestBodyParams', [$scopes]);

		// twitch puts key/secret into the body instead of a basic auth header
		$this::assertSame($this->options->key, $params['client_id']);
		$this::assertSame($this->options->secret, $params['client_secret']);

		$this::assertSame('client_credentials', $params['grant_type']);
		$this::assertSame(implode($this->provider::SCOPE_DELIMITER, $scopes), $params['scope']);
	}

	public function testClientCredentialsTokenRequest():void{
		$url      = 'https://localhost/access_token';
		$response = $this->invokeReflectionMethod('sendClientCredentialsTokenRequest', [$url, ['foo' => 'bar']]);
		$json     = MessageUtil::decodeJSON($response);

		$this::assertSame('identity', $json->headers->{'Accept-Encoding'});
		$this::assertSame('application/x-www-form-urlencoded', $json->headers->{'Content-Type'});
		$this::assertSame($url, $json->request->url);
		$this::assertSame('POST', $json->request->method);
		$this::assertSame('foo=bar', $json->body);
	}

}
