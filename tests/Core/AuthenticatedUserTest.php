<?php
/**
 * Class AuthenticatedUserTest
 *
 * @created      23.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuth\Core\AuthenticatedUser;
use PHPUnit\Framework\TestCase;

/**
 *
 */
final class AuthenticatedUserTest extends TestCase{

	public function testClassIsReadOnly():void{

		$userdata = [
			'id'     => 123,
			'handle' => 'testuser',
			'email'  => 'test@example.com',
		];

		// set data via constructor
		$user = new AuthenticatedUser($userdata);

		$this::assertSame(123, $user->id);
		$this::assertSame('testuser', $user->handle);
		$this::assertSame('test@example.com', $user->email);

		// cannot overwrite via magic set
		$user->id = 345;

		$this::assertSame(123, $user->id);

		// cannot overwrite via fromIterable()
		$userdata = [
			'id'   => 456,
			'userName' => 'nope',
			'email'    => 'me@nowhere.com',
		];

		/** @noinspection PhpExpressionResultUnusedInspection */
		$user->fromIterable($userdata);

		$this::assertSame(123, $user->id);
		$this::assertSame('testuser', $user->handle);
		$this::assertSame('test@example.com', $user->email);
	}

}
