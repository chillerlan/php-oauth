<?php
/**
 * Class WordpressAPITest
 *
 * @created      21.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\WordPress;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\WordPress $provider
 */
#[Group('providerLiveTest')]
final class WordpressAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return WordPress::class;
	}

}
