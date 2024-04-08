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

	protected function initStorage(OAuthOptions $options):OAuthStorageInterface{
		return new SessionStorage($options);
	}

	protected function initOptions():OAuthOptions{
		$options = new OAuthOptions;

		$options->sessionStart      = true;
		$options->sessionStorageVar = 'session_test';

		return $options;
	}

	public function testStoreStateWithNonExistentArray():void{
		$options = $this->initOptions();

		unset($_SESSION[$options->sessionStorageVar]);

		$this::assertFalse($this->storage->hasCSRFState($this->providerName));
		$this->storage->storeCSRFState('foobar', $this->providerName);
		$this::assertTrue($this->storage->hasCSRFState($this->providerName));
	}

}
