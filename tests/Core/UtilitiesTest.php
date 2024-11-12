<?php
/**
 * Class UtilitiesTest
 *
 * @created      10.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuth\Core\{OAuthInterface, Utilities};
use PHPUnit\Framework\TestCase;
use InvalidArgumentException, ReflectionClass;

/**
 * Tests the Utilities class
 */
class UtilitiesTest extends TestCase{

	public function testGetProviders():void{
		$providers = Utilities::getProviders();

		foreach($providers as $provider){
			$this::assertTrue((new ReflectionClass($provider['fqcn']))->implementsInterface(OAuthInterface::class));
		}
	}

	public function testGetProvidersWithGivenPath():void{
		$providers = Utilities::getProviders(__DIR__.'/../Providers', 'chillerlan\\OAuthTest\\Providers\\');

		foreach($providers as $provider){
			$this::assertTrue((new ReflectionClass($provider['fqcn']))->implementsInterface(OAuthInterface::class));
		}
	}

	public function testGetProvidersInvalidPathException():void{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('invalid file path');

		Utilities::getProviders('/foo');
	}

}
