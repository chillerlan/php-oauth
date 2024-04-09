<?php
/**
 * Class PinterestTest
 *
 * @created      08.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Pinterest;

/**
 * @property \chillerlan\OAuth\Providers\Pinterest $provider
 */
class PinterestTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Pinterest::class;
	}

}
