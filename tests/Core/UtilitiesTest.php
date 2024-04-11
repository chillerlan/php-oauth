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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException, ReflectionClass;

/**
 *
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
		$this->expectExceptionMessage('invalid $providerDir');

		Utilities::getProviders('/foo');
	}

	public static function encryptionFormatProvider():array{
		return [
			'binary' => [Utilities::ENCRYPT_FORMAT_BINARY],
			'base64' => [Utilities::ENCRYPT_FORMAT_BASE64],
			'hex'    => [Utilities::ENCRYPT_FORMAT_HEX],
		];
	}

	#[DataProvider('encryptionFormatProvider')]
	public function testEncryptDecrypt(int $format):void{
		$data = 'hello this is a test string!';
		$key  = Utilities::createEncryptionKey();

		$encrypted = Utilities::encrypt($data, $key, $format);
		$decrypted = Utilities::decrypt($encrypted, $key, $format);

		$this::assertSame($data, $decrypted);
	}

}
