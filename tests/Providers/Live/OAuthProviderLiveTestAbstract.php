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

use chillerlan\OAuth\Core\{AccessToken, AuthenticatedUser, UserInfo};
use chillerlan\OAuth\Core\UnauthorizedAccessException;
use chillerlan\OAuth\Storage\MemoryStorage;
use chillerlan\OAuthTest\Providers\ProviderLiveTestAbstract;
use InvalidArgumentException;
use PHPUnit\Framework\ExpectationFailedException;
use function constant, sprintf;

/**
 * abstract OAuth live API test
 *
 * @property \chillerlan\OAuth\Core\OAuthInterface & UserInfo $provider
 */
abstract class OAuthProviderLiveTestAbstract extends ProviderLiveTestAbstract{

	/*
	 * "me" endpoint tests
	 */

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertSame($this->TEST_USER, $user->handle);
	}

	public function testMe():void{

		if(!$this->provider instanceof UserInfo){
			$this::markTestSkipped('AuthenticatedUser N/A');
		}

		try{
			/** @phan-suppress-next-line PhanUndeclaredMethod ($this->provider is, in fact, instance of UserInfo) */
			$user = $this->provider->me();
		}
		catch(UnauthorizedAccessException){
			$this::markTestSkipped('unauthorized: token is missing or expired');
		}

		try{
			/** @phan-suppress-next-line PhanPossiblyUndeclaredVariable */
			$this->assertMeResponse($user);
		}
		catch(ExpectationFailedException $e){
			$var = $this->ENV_PREFIX.'_TESTUSER';

			if($this->dotEnv->get($var) === null){
				throw new InvalidArgumentException(sprintf('variable "%s" is not set in "%s"', $var, constant('TEST_ENVFILE')));
			}

			throw $e;
		}
	}

	public function testMeUnauthorizedAccessException():void{

		if(!$this->provider instanceof UserInfo){
			$this::markTestSkipped('AuthenticatedUser N/A');
		}

		$token                    = $this->storage->getAccessToken($this->provider->getName());
		// avoid refresh
		$token->expires           = AccessToken::NEVER_EXPIRES;
		$token->refreshToken      = null;
		// invalidate token
		$token->accessToken       = 'nope';
		$token->accessTokenSecret = 'what';

		// using a temp storage here so that the local tokens won't be overwritten
		$tempStorage = (new MemoryStorage)->storeAccessToken($token, $this->provider->getName());

		$this->provider->setStorage($tempStorage);

		$this->expectException(UnauthorizedAccessException::class);

		/** @phan-suppress-next-line PhanUndeclaredMethod ($this->provider is, in fact, instance of UserInfo) */
		$this->provider->me();
	}

}
