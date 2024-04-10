<?php
/**
 * Class Tumblr2APITest
 *
 * @created      30.07.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Tumblr2;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Tumblr2 $provider
 */
#[Group('providerLiveTest')]
final class Tumblr2APITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Tumblr2::class;
	}

}
