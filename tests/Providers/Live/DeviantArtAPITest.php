<?php
/**
 * Class DeviantArtAPITest
 *
 * @created      27.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\DeviantArt;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\DeviantArt $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
final class DeviantArtAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return DeviantArt::class;
	}

	protected function getEnvPrefix():string{
		return 'DEVIANTART';
	}

}
