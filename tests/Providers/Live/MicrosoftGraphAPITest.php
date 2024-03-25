<?php
/**
 * Class MicrosoftGraphAPITest
 *
 * @created      30.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\MicrosoftGraph;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\MicrosoftGraph $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
class MicrosoftGraphAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return MicrosoftGraph::class;
	}

	protected function getEnvPrefix():string{
		return 'MICROSOFT_AAD';
	}

}
