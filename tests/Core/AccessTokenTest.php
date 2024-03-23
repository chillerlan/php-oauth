<?php
/**
 * Class AccessTokenTest
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuth\Core\AccessToken;
use PHPUnit\Framework\Attributes\{DataProvider, Group};
use PHPUnit\Framework\TestCase;
use function sleep;
use function time;

/**
 * Tests the AccessToken class
 */
final class AccessTokenTest extends TestCase{

	private AccessToken $token;

	protected function setUp():void{
		$this->token = new AccessToken;
	}

	public static function tokenDataProvider():array{
		return [
			'accessTokenSecret' => ['accessTokenSecret', null, 'ACCESS_TOKEN'],
			'accessToken'       => ['accessToken',       null, 'ACCESS_TOKEN_SECRET'],
			'refreshToken'      => ['refreshToken',      null, 'REFRESH_TOKEN'],
			'extraParams'       => ['extraParams',       []  , ['foo' => 'bar']],
		];
	}

	#[DataProvider('tokenDataProvider')]
	public function testDefaultsGetSet(string $property, mixed $value, mixed $data):void{
		// test defaults
		$this::assertSame($value, $this->token->{$property});

		// set some data
		$this->token->{$property} = $data;

		$this::assertSame($data, $this->token->{$property});
	}

	public static function expiryDataProvider():array{
		return [
			'EOL_UNKNOWN (null)'        => [null,       AccessToken::EOL_UNKNOWN],
			'EOL_UNKNOWN (-9001)'       => [-9001,      AccessToken::EOL_UNKNOWN],
			'EOL_UNKNOWN (-1)'          => [-1,         AccessToken::EOL_UNKNOWN],
			'EOL_UNKNOWN (1514309386)'  => [1514309386, AccessToken::EOL_UNKNOWN],
			'EOL_NEVER_EXPIRES (-9002)' => [-9002,      AccessToken::EOL_NEVER_EXPIRES],
			'EOL_NEVER_EXPIRES (0)'     => [0,          AccessToken::EOL_NEVER_EXPIRES],
		];
	}

	#[DataProvider('expiryDataProvider')]
	public function testSetExpiry(int|null $expires, int $expected):void{
		$this->token->expires = $expires;

		$this::assertSame($expected, $this->token->expires);
	}

	public static function isExpiredDataProvider():array{
		return [
			'0 (f)'                 => [0,                              false],
			'EOL_NEVER_EXPIRES (f)' => [AccessToken::EOL_NEVER_EXPIRES, false],
			'EOL_UNKNOWN (t)'       => [AccessToken::EOL_UNKNOWN,       true],
		];
	}

	#[DataProvider('isExpiredDataProvider')]
	public function testIsExpired(int $expires, bool $isExpired):void{
		$this->token->setExpiry($expires);
		$this::assertSame($isExpired, $this->token->isExpired());
	}

	#[Group('slow')]
	public function testIsExpiredVariable():void{
		$expiry = (time() + 3600);
		$this->token->setExpiry($expiry);
		$this::assertSame($expiry, $this->token->expires);
		$this::assertFalse($this->token->isExpired());

		$expiry = 3600;
		$this->token->setExpiry($expiry);
		$this::assertSame((time() + $expiry), $this->token->expires);
		$this::assertFalse($this->token->isExpired());

		$expiry = 2;
		$this->token->setExpiry($expiry);
		$this::assertSame((time() + $expiry), $this->token->expires);
		sleep(3);
		$this::assertTrue($this->token->isExpired());

		$expiry = (time() + 2);
		$this->token->setExpiry($expiry);
		$this::assertSame($expiry, $this->token->expires);
		sleep(3);
		$this::assertTrue($this->token->isExpired());
	}

}
