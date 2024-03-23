<?php
/**
 * Class Patreon2Test
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Patreon;

/**
 * @property \chillerlan\OAuth\Providers\Patreon $provider
 */
final class Patreon2Test extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Patreon::class;
	}

}
