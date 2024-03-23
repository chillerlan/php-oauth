<?php
/**
 * Class LastFMTest
 *
 * @created      05.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\LastFM;

/**
 * @property \chillerlan\OAuth\Providers\LastFM $provider
 */
class LastFMTest extends OAuthProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return LastFM::class;
	}

}
