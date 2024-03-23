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

use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageInterface};

final class MemoryStorageTest extends StorageTestAbstract{

	protected function initStorage():OAuthStorageInterface{
		return new MemoryStorage;
	}

}
