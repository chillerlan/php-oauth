<?php
/**
 * Class OAuth2ProviderLiveTestAbstract
 *
 * @created      17.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Core\ClientCredentials;
use chillerlan\OAuth\Storage\MemoryStorage;
use function time;

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
abstract class OAuth2ProviderLiveTestAbstract extends OAuthProviderLiveTestAbstract{

	protected array $clientCredentialsScopes = [];

	public function testRequestCredentialsToken():void{

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('ClientCredentials N/A');
		}

		$this->provider->setStorage(new MemoryStorage);

		$token = $this->provider->getClientCredentialsToken($this->clientCredentialsScopes);

		$this::assertInstanceOf(AccessToken::class, $token);
		$this::assertIsString($token->accessToken);

		if($token->expires !== AccessToken::EOL_NEVER_EXPIRES){
			$this::assertGreaterThan(time(), $token->expires);
		}

		$this->logger->debug('OAuth2ClientCredentials', $token->toArray());
	}

}
