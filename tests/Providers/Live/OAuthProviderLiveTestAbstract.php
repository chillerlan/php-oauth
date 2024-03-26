<?php
/**
 * Class OAuthProviderLiveTestAbstract
 *
 * @created      18.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\{AccessToken, AuthenticatedUser};
use chillerlan\OAuth\Core\UnauthorizedAccessException;
use chillerlan\OAuth\Storage\MemoryStorage;
use chillerlan\OAuthTest\Providers\ProviderLiveTestAbstract;
use InvalidArgumentException;
use PHPUnit\Framework\ExpectationFailedException;
use function constant, sprintf;

/**
 * @property \chillerlan\OAuth\Core\OAuthInterface $provider
 */
abstract class OAuthProviderLiveTestAbstract extends ProviderLiveTestAbstract{

	/*
	 * "me" endpoint tests
	 */

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertSame($this->TEST_USER, $user->handle);
	}

	public function testMe():void{

			try{
				$user = $this->provider->me();
			}
			catch(UnauthorizedAccessException){
				$this::markTestSkipped('unauthorized: token is missing or expired');
			}

			if($user === null){
				$this::markTestSkipped('AuthenticatedUser N/A');
			}

			try{
				$this->assertMeResponse($user);
			}
			catch(ExpectationFailedException $e){
				$var = $this->getEnvPrefix().'_TESTUSER';

				if($this->dotEnv->get($var) === null){
					throw new InvalidArgumentException(sprintf('variable "%s" is not set in "%s"', $var, constant('TEST_ENVFILE')));
				}

				throw $e;
			}
	}

	protected function assertUnauthorizedAccessException(AccessToken $token):void{
		$this->expectException(UnauthorizedAccessException::class);

		$this->provider->me();
	}

	public function testUnauthorizedAccessException():void{
		$token                    = $this->storage->getAccessToken($this->provider->serviceName);
		// avoid refresh
		$token->expires           = AccessToken::NEVER_EXPIRES;
		$token->refreshToken      = null;
		// invalidate token
		$token->accessToken       = 'nope';
		$token->accessTokenSecret = 'what';

		// using a temp storage here so that the local tokens won't be overwritten
		$tempStorage = (new MemoryStorage)->storeAccessToken($token, $this->provider->serviceName);

		$this->provider->setStorage($tempStorage);

		$this->assertUnauthorizedAccessException($token);
	}

}
