<?php
/**
 * Class CodebergTest
 *
 * @created      08.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Codeberg;

/**
 * @property \chillerlan\OAuth\Providers\Codeberg $provider
 */
class CodebergTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Codeberg::class;
	}

}
