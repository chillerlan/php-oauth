<?php
/**
 * Class BigCartelAPITest
 *
 * @created      10.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\BigCartel;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\BigCartel $provider
 */
#[Group('providerLiveTest')]
final class BigCartelAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return BigCartel::class;
	}

}
