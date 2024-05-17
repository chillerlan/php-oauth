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
use PHPUnit\Framework\{ExpectationFailedException, TestCase};
use DateInterval, DateTime;
use function sleep, time;

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
		$now = time();

		return [
			'EXPIRY_UNKNOWN (null)'       => [null,       AccessToken::EXPIRY_UNKNOWN],
			'EXPIRY_UNKNOWN (-9001)'      => [-9001,      AccessToken::EXPIRY_UNKNOWN],
			'EXPIRY_UNKNOWN (-1)'         => [-1,         AccessToken::EXPIRY_UNKNOWN],
			'EXPIRY_UNKNOWN (1514309386)' => [1514309386, AccessToken::EXPIRY_UNKNOWN],
			'NEVER_EXPIRES  (-9002)'      => [-9002,      AccessToken::NEVER_EXPIRES],
			'NEVER_EXPIRES  (0)'          => [0,          AccessToken::NEVER_EXPIRES],
			'timestamp (now + 42)'        => [($now + 42),                             ($now + 42)],
			'int (42)'                    => [42,                                      ($now + 42)],
			'DateTime (now + 42)'         => [(new DateTime)->setTimestamp($now + 42), ($now + 42)],
			'DateInterval (42)'           => [new DateInterval('PT42S'),               ($now + 42)],
			'clamp max expiry'            => [($now + $now),      ($now + AccessToken::EXPIRY_MAX)],
		];
	}

	#[DataProvider('expiryDataProvider')]
	public function testSetExpiry(DateTime|DateInterval|int|null $expires, int $expected):void{
		$this->token->expires = $expires;

		// time tests are a bit wonky sometimes
		try{
			$this::assertSame($expected, $this->token->expires);
		}
		catch(ExpectationFailedException $e){
			$diff = $expected - $this->token->expires;

			$this::assertTrue(($diff >= -2 || $diff <= 2), 'give a bit of leeway');
		}

	}

	public static function isExpiredDataProvider():array{
		return [
			'0 (f)'              => [0,                           false],
			'NEVER_EXPIRES (f)'  => [AccessToken::NEVER_EXPIRES,  false],
			'EXPIRY_UNKNOWN (t)' => [AccessToken::EXPIRY_UNKNOWN, true],
		];
	}

	#[DataProvider('isExpiredDataProvider')]
	public function testIsExpired(int $expiry, bool $isExpired):void{
		$this->token->expires = $expiry;
		$this::assertSame($isExpired, $this->token->isExpired());
	}

	#[Group('slow')]
	public function testIsExpiredVariable():void{
		$expiry = (time() + 3600);
		$this->token->expires = $expiry;
		$this::assertSame($expiry, $this->token->expires);
		$this::assertFalse($this->token->isExpired());

		$expiry = 3600;
		$this->token->expires = $expiry;
		$this::assertSame((time() + $expiry), $this->token->expires);
		$this::assertFalse($this->token->isExpired());

		$expiry = 2;
		$this->token->expires = $expiry;
		$this::assertSame((time() + $expiry), $this->token->expires);
		sleep(3);
		$this::assertTrue($this->token->isExpired());

		$expiry = (time() + 2);
		$this->token->expires = $expiry;
		$this::assertSame($expiry, $this->token->expires);
		sleep(3);
		$this::assertTrue($this->token->isExpired());
	}

}
