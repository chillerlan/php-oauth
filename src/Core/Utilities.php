<?php
/**
 * Class Utilities
 *
 * @created      10.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @filesource
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use DirectoryIterator;
use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;
use function hash;
use function random_bytes;
use function realpath;
use function sodium_base642bin;
use function sodium_bin2base64;
use function sodium_bin2hex;
use function sodium_crypto_secretbox;
use function sodium_crypto_secretbox_keygen;
use function sodium_crypto_secretbox_open;
use function sodium_hex2bin;
use function sodium_memzero;
use function substr;
use function trim;
use const SODIUM_BASE64_VARIANT_ORIGINAL;
use const SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

/**
 * Common utilities for use with the OAuth providers
 */
class Utilities{

	final public const ENCRYPT_FORMAT_BINARY = 0b00;
	final public const ENCRYPT_FORMAT_BASE64 = 0b01;
	final public const ENCRYPT_FORMAT_HEX    = 0b10;

	/**
	 * Fetches a list of provider classes in the given directory
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function getProviders(string|null $providerDir = null, string|null $namespace = null):array{
		$providerDir = realpath(($providerDir ?? __DIR__.'/../Providers'));
		$namespace   = trim(($namespace ?? 'chillerlan\\OAuth\\Providers'), '\\');
		$providers   = [];

		if($providerDir === false){
			throw new InvalidArgumentException('invalid $providerDir');
		}

		foreach(new DirectoryIterator($providerDir) as $e){

			if($e->getExtension() !== 'php'){
				continue;
			}

			$r = new ReflectionClass($namespace.'\\'.substr($e->getFilename(), 0, -4));

			if(!$r->implementsInterface(OAuthInterface::class) || $r->isAbstract()){
				continue;
			}

			$providers[hash('crc32b', $r->getShortName())] = [
				'name' => $r->getShortName(),
				'fqcn' => $r->getName(),
				'path' => $e->getRealPath(),
			];

		}

		return $providers;
	}

	/**
	 * Creates a new cryptographically secure random encryption key (in hexadecimal format)
	 */
	public static function createEncryptionKey():string{
		return sodium_bin2hex(sodium_crypto_secretbox_keygen());
	}

	/**
	 * encrypts the given $data with $key, $format output [binary, base64, hex]
	 *
	 * @see \sodium_crypto_secretbox()
	 * @see \sodium_bin2base64()
	 * @see \sodium_bin2hex()
	 */
	public static function encrypt(string $data, string $keyHex, int $format = self::ENCRYPT_FORMAT_HEX):string{
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$box   = sodium_crypto_secretbox($data, $nonce, sodium_hex2bin($keyHex));

		$out = match($format){
			self::ENCRYPT_FORMAT_BINARY => $nonce.$box,
			self::ENCRYPT_FORMAT_BASE64 => sodium_bin2base64($nonce.$box, SODIUM_BASE64_VARIANT_ORIGINAL),
			self::ENCRYPT_FORMAT_HEX    => sodium_bin2hex($nonce.$box),
			default                     => throw new InvalidArgumentException('invalid format'),
		};

		sodium_memzero($data);
		sodium_memzero($keyHex);
		sodium_memzero($nonce);
		sodium_memzero($box);

		return $out;
	}

	/**
	 * decrypts the given $encrypted data with $key from $format input [binary, base64, hex]
	 *
	 * @see \sodium_crypto_secretbox_open()
	 * @see \sodium_base642bin()
	 * @see \sodium_hex2bin()
	 */
	public static function decrypt(string $encrypted, string $keyHex, int $format = self::ENCRYPT_FORMAT_HEX):string{

		$bin = match($format){
			self::ENCRYPT_FORMAT_BINARY => $encrypted,
			self::ENCRYPT_FORMAT_BASE64 => sodium_base642bin($encrypted, SODIUM_BASE64_VARIANT_ORIGINAL),
			self::ENCRYPT_FORMAT_HEX    => sodium_hex2bin($encrypted),
			default                     => throw new InvalidArgumentException('invalid format'),
		};

		$nonce = substr($bin, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$box   = substr($bin, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$data  = sodium_crypto_secretbox_open($box, $nonce, sodium_hex2bin($keyHex));

		sodium_memzero($encrypted);
		sodium_memzero($keyHex);
		sodium_memzero($bin);
		sodium_memzero($nonce);
		sodium_memzero($box);

		if($data === false){
			throw new RuntimeException('decryption failed'); // @codeCoverageIgnore
		}

		return $data;
	}

}
