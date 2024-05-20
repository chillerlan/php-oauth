<?php
/**
 * Class FileStorageEncryptedTest
 *
 * @created      01.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Storage;

use chillerlan\OAuth\OAuthOptions;

/**
 * Tests the `FileStorage` class (encrypted)
 */
final class FileStorageEncryptedTest extends FileStorageTest{

	protected function initOptions():OAuthOptions{
		$options = new OAuthOptions;

		$options->fileStoragePath      = $this::STORAGE_PATH;
		$options->useStorageEncryption = true;
		$options->storageEncryptionKey = $this::ENCRYPTION_KEY;

		return $options;
	}

}
