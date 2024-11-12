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

use chillerlan\Utilities\File;
use DirectoryIterator;
use InvalidArgumentException;
use ReflectionClass;
use function hash;
use function substr;
use function trim;

/**
 * Common utilities for use with the OAuth providers
 */
class Utilities{

	/**
	 * Fetches a list of provider classes in the given directory
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function getProviders(string|null $providerDir = null, string|null $namespace = null):array{
		$providerDir = File::realpath(($providerDir ?? __DIR__.'/../Providers'));
		$namespace   = trim(($namespace ?? 'chillerlan\\OAuth\\Providers'), '\\');
		$providers   = [];

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

}
