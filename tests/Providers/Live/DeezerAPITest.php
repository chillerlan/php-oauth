<?php
/**
 * Class DeezerAPITest
 *
 * @created      10.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Deezer;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Deezer $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
final class DeezerAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Deezer::class;
	}

}
