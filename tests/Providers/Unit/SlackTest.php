<?php
/**
 * Class SlackTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Slack;

/**
 * @property \chillerlan\OAuth\Providers\Slack $provider
 */
final class SlackTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Slack::class;
	}

}
