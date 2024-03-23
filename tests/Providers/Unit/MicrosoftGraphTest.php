<?php
/**
 * Class MicrosoftGraphTest
 *
 * @created      30.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\MicrosoftGraph;

/**
 * @property \chillerlan\OAuth\Providers\MicrosoftGraph $provider
 */
final class MicrosoftGraphTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return MicrosoftGraph::class;
	}

}
