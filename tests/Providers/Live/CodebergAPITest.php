<?php
/**
 * Class CodebergAPITest
 *
 * @created      08.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Codeberg;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Codeberg $provider
 */
#[Group('providerLiveTest')]
class CodebergAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Codeberg::class;
	}

}
