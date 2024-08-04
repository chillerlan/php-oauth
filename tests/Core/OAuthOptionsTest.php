<?php
/**
 * Class OAuthOptionsTest
 *
 * @created      10.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\OAuthStorageException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the OAuthOptions class
 */
class OAuthOptionsTest extends TestCase{

	public function testSetStorageEncryptionKey():void{
		$key = '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f';

		$options = new OAuthOptions(['storageEncryptionKey' => $key]);

		$this::assertSame($key, $options->storageEncryptionKey);
	}

	public function testSetStorageEncryptionKeyInvalidException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('invalid encryption key');
		/** @phan-suppress-next-line PhanNoopNew */
		new OAuthOptions(['storageEncryptionKey' => 'foo']);
	}

	public function testSetFileStoragePath():void{
		$options = new OAuthOptions(['fileStoragePath' => __DIR__]);

		$this::assertSame(__DIR__, $options->fileStoragePath);
	}

	public function testSetFileStoragePathInvalidException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('invalid storage path "/foo"');
		/** @phan-suppress-next-line PhanNoopNew */
		new OAuthOptions(['fileStoragePath' => '/foo']);
	}

	public function testClampPKCEVerifierLength():void{
		$options = new OAuthOptions;

		// lower limit = 43
		$options->pkceVerifierLength = -42;
		$this::assertSame(43, $options->pkceVerifierLength);

		// upper limit = 128
		$options->pkceVerifierLength = 69420;
		$this::assertSame(128, $options->pkceVerifierLength);
	}

}
