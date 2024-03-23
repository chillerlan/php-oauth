<?php
/**
 * Class OAuthProviderLiveTestAbstract
 *
 * @created      18.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Core\UnauthorizedAccessException;
use chillerlan\OAuth\Storage\MemoryStorage;
use chillerlan\OAuthTest\Providers\ProviderLiveTestAbstract;
use Psr\Http\Message\ResponseInterface;
use function str_contains;

/**
 * @property \chillerlan\OAuth\Core\OAuthInterface $provider
 */
abstract class OAuthProviderLiveTestAbstract extends ProviderLiveTestAbstract{

	/*
	 * "me" endpoint tests
	 */

	abstract protected function assertMeResponse(ResponseInterface $response, object|null $json):void;

	public function testMe():void{

			try{
				$response = $this->provider->me();
			}
			catch(UnauthorizedAccessException){
				$this::markTestSkipped('unauthorized: token is missing or expired');
			}

			$json = null;

			if($response->hasHeader('Content-Type')){
				$type = $response->getHeaderLine('Content-Type');

				if(str_contains($type, 'application/json') || str_contains($type, 'application/vnd.api+json')){
					$json = MessageUtil::decodeJSON($response);
				}

			}

			$this->assertMeResponse($response, $json);
	}

	protected function assertMeErrorException(AccessToken $token):void{
		$this->expectException(UnauthorizedAccessException::class);

		// using a temp storage here so that the local tokens won't be overwritten
		$tempStorage = (new MemoryStorage)->storeAccessToken($token, $this->provider->serviceName);

		$this->provider->setStorage($tempStorage)->me();
	}

	public function testMeErrorException():void{
		$token                    = $this->storage->getAccessToken($this->provider->serviceName);
		// avoid refresh
		$token->expires           = AccessToken::EOL_NEVER_EXPIRES;
		$token->refreshToken      = null;
		// invalidate token
		$token->accessToken       = 'nope';
		$token->accessTokenSecret = 'what';

		$this->assertMeErrorException($token);
	}

}
