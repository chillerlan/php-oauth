<?php
/**
 * Class DiscordTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Discord;

/**
 * @property \chillerlan\OAuth\Providers\Discord $provider
 */
final class DiscordTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Discord::class;
	}

}
