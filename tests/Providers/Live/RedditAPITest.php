<?php
/**
 * Class RedditAPITest
 *
 * @created      09.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Reddit;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Reddit $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
class RedditAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Reddit::class;
	}

	protected function getEnvPrefix():string{
		return 'REDDIT';
	}

}
