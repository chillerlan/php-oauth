<?php
/**
 * Class FileStorageTest
 *
 * @created      31.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Storage;

use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\{FileStorage, OAuthStorageException, OAuthStorageInterface};
use ReflectionMethod;
use function implode;
use const DIRECTORY_SEPARATOR;

class FileStorageTest extends StorageTestAbstract{

	protected const STORAGE_PATH = __DIR__.DIRECTORY_SEPARATOR.'.filestorage';

	protected function initStorage(OAuthOptions $options):OAuthStorageInterface{
		return new FileStorage('oauth_user', $options);
	}

	protected function initOptions():OAuthOptions{
		$options = new OAuthOptions;

		$options->fileStoragePath      = $this::STORAGE_PATH;
		$options->useStorageEncryption = false;

		return $options;
	}

	public function testInvalidUserException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('invalid OAuth user');

		new FileStorage('');
	}

	public function testEmptyStoragePathException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('no storage path given');

		new FileStorage('oauth_user');
	}

	public function testGetFilePath():void{
		$path = (new ReflectionMethod(FileStorage::class, 'getFilepath'))
			->invokeArgs($this->storage, ['key', 'provider']);

		$expected = implode(DIRECTORY_SEPARATOR, [$this::STORAGE_PATH, 'provider', '3', '3f', '3fc']);

		$this::assertStringContainsString($expected, $path);
	}

}
