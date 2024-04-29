<?php
/**
 * Class Provider
 *
 * @created      29.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types = 1);

namespace chillerlan\OAuthTest\Attributes;

use Attribute;

/**
 * Supplies the provider class name
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Provider{

	public function __construct(
		private readonly string $className,
	){}

	public function className():string{
		return $this->className;
	}

}
