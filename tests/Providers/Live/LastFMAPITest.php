<?php
/**
 * Class LastFMAPITest
 *
 * @created      10.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\LastFM;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\LastFM $provider
 */
#[Group('providerLiveTest')]
final class LastFMAPITest extends OAuthProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return LastFM::class;
	}

	protected function getEnvPrefix():string{
		return 'LASTFM';
	}

}
