<?php
/**
 * Class DiscogsAPITest
 *
 * @created      10.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Discogs;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Discogs $provider
 */
#[Group('providerLiveTest')]
class DiscogsAPITest extends OAuth1ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Discogs::class;
	}

	protected function getEnvPrefix():string{
		return 'DISCOGS';
	}

}
