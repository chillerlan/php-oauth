<?php
/**
 * Class MemoryStorageTest
 *
 * @created      08.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Storage;

use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageInterface};

/**
 * Tests the `MemoryStorage` class
 */
final class MemoryStorageTest extends StorageTestAbstract{

	protected function initStorage(OAuthOptions $options):OAuthStorageInterface{
		return new MemoryStorage($options);
	}

}
