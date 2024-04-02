<?php
/**
 * Class StorageTestAbstract
 *
 * @created      24.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Storage;

use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Storage\{OAuthStorageException, OAuthStorageInterface};
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use function array_merge;

abstract class StorageTestAbstract extends TestCase{

	protected const ENCRYPTION_KEY = "\x00\x01\x02\x03\x04\x05\x06\x07".
	                                 "\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f".
	                                 "\x10\x11\x12\x13\x14\x15\x16\x17".
	                                 "\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f";

	protected OAuthStorageInterface $storage;
	protected AccessToken           $token;
	protected string                $providerName = 'testService'; // test provider name

	protected function setUp():void{
		$this->token   = new AccessToken(['accessToken' => 'foobar']);
		$this->storage = $this->initStorage($this->initOptions());
	}

	abstract protected function initStorage(OAuthOptions $options):OAuthStorageInterface;

	protected function initOptions():OAuthOptions{
		return new OAuthOptions;
	}

	public function testTAccessToken():void{
		$this->storage->storeAccessToken($this->token, $this->providerName);
		$this::assertTrue($this->storage->hasAccessToken($this->providerName));
		$this::assertSame('foobar', $this->storage->getAccessToken($this->providerName)->accessToken);

		$this->storage->clearAccessToken($this->providerName);
		$this::assertFalse($this->storage->hasAccessToken($this->providerName));
	}

	public function testClearAllAccessTokens():void{

		foreach(['a', 'b', 'c', $this->providerName] as $provider){
			$this->storage->storeAccessToken($this->token, $provider);
			$this::assertTrue($this->storage->hasAccessToken($provider));
		}

		$this->storage->clearAllAccessTokens();

		foreach(['a', 'b', 'c', $this->providerName] as $provider){
			$this::assertFalse($this->storage->hasAccessToken($provider));
		}

	}

	public function testCSRFState(){
		$this->storage->storeCSRFState('foobar', $this->providerName);
		$this::assertTrue($this->storage->hasCSRFState($this->providerName));
		$this::assertSame('foobar', $this->storage->getCSRFState($this->providerName));

		$this->storage->clearCSRFState($this->providerName);
		$this::assertFalse($this->storage->hasCSRFState($this->providerName));
	}

	public function testClearAllCSRFStates():void{

		foreach(['a', 'b', 'c', $this->providerName] as $provider){
			$this->storage->storeCSRFState('foobar', $provider);
			$this::assertTrue($this->storage->hasCSRFState($provider));
		}

		$this->storage->clearAllCSRFStates();

		foreach(['a', 'b', 'c', $this->providerName] as $provider){
			$this::assertFalse($this->storage->hasCSRFState($provider));
		}

	}

	public function testGetProviderNameEmptyNameException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('provider name must not be empty');

		(new ReflectionMethod($this->storage, 'getProviderName'))
			->invokeArgs($this->storage, [' ']);
	}

	public function testNoEncryptionKeyException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('no encryption key given');

		$options = new OAuthOptions;

		$options->useStorageEncryption = true;

		$this->initStorage($options);
	}

	public function testRetrieveCSRFStateNotFoundException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('state not found');

		$this->storage->getCSRFState('LOLNOPE');
	}

	public function testRetrieveAccessTokenNotFoundException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('token not found');

		$this->storage->getAccessToken('LOLNOPE');
	}

	public function testToStorage():void{
		$a = $this->storage->toStorage($this->token);
		$b = $this->storage->fromStorage($a);

		$this::assertIsString($a);
		$this::assertInstanceOf(AccessToken::class, $b);
		$this::assertEquals($this->token, $b);
	}

	public function testStoreWithExistingToken():void{
		$this->storage->storeAccessToken($this->token, $this->providerName);

		$this->token->extraParams = array_merge($this->token->extraParams, ['q' => 'u here?']);

		$this->storage->storeAccessToken($this->token, $this->providerName);

		$token = $this->storage->getAccessToken($this->providerName);

		$this::assertSame('u here?', $token->extraParams['q']);

		$this->storage->clearAccessToken($this->providerName);
		$this::assertFalse($this->storage->hasAccessToken($this->providerName));
	}

}
