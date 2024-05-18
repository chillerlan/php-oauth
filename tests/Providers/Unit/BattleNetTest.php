<?php
/**
 * Class BattleNetTest
 *
 * @created      02.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\BattleNet;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @property \chillerlan\OAuth\Providers\BattleNet $provider
 */
#[Provider(BattleNet::class)]
final class BattleNetTest extends OAuth2ProviderUnitTestAbstract{

	public function testSetRegion():void{
		$this->provider->setRegion('cn');
		$this::assertSame('https://gateway.battlenet.com.cn', $this->getReflectionProperty('apiURL'));

		$this->provider->setRegion('us');
		$this::assertSame('https://us.api.blizzard.com', $this->getReflectionProperty('apiURL'));
	}

	public function testSetRegionException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid region: foo');

		$this->provider->setRegion('foo');
	}

	public static function requestTargetProvider():array{
		return [
			'empty'          => ['', 'https://eu.api.blizzard.com'],
			'slash'          => ['/', 'https://eu.api.blizzard.com/'],
			'no slashes'     => ['a', 'https://eu.api.blizzard.com/a'],
			'leading slash'  => ['/b', 'https://eu.api.blizzard.com/b'],
			'trailing slash' => ['c/', 'https://eu.api.blizzard.com/c/'],
			'full url given' => ['https://oauth.battle.net/other/path/d', 'https://oauth.battle.net/other/path/d'],
			'ignore params'  => ['https://oauth.battle.net/api/e/?with=param#foo', 'https://oauth.battle.net/api/e/'],
			'enforce https'  => ['wtf://eu.api.blizzard.com/a/b/c', 'https://eu.api.blizzard.com/a/b/c'],
		];
	}

	#[DataProvider('requestTargetProvider')]
	public function testGetRequestTarget(string $path, string $expected):void{
		$this::assertSame($expected, $this->invokeReflectionMethod('getRequestTarget', [$path]));
	}

}
