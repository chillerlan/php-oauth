<?php
/**
 * Class SessionStorageEncryptedTest
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
 * Tests the `SessionStorage` class (encrypted)
 */
final class SessionStorageEncryptedTest extends SessionStorageTest{

	protected function initOptions():OAuthOptions{
		$options = new OAuthOptions;

		$options->useStorageEncryption = true;
		$options->storageEncryptionKey = $this::ENCRYPTION_KEY;
		$options->sessionStart         = true;
		$options->sessionStorageVar    = 'session_test';

		return $options;
	}

}
