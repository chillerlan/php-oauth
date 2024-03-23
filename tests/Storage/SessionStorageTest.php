<?php
/**
 * Class SessionStorageTest
 *
 * @created      08.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Storage;

use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\{OAuthStorageInterface, SessionStorage};

final class SessionStorageTest extends StorageTestAbstract{

	protected function initStorage():OAuthStorageInterface{
		return new SessionStorage(new OAuthOptions(['sessionStart' => true]));
	}

	public function testStoreStateWithNonExistentArray():void{
		$options = new OAuthOptions;
		unset($_SESSION[$options->sessionStateVar]);

		$this::assertFalse($this->storage->hasCSRFState($this->tsn));
		$this->storage->storeCSRFState('foobar', $this->tsn);
		$this::assertTrue($this->storage->hasCSRFState($this->tsn));
	}

}
