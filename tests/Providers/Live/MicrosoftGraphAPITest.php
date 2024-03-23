<?php
/**
 * Class MicrosoftGraphAPITest
 *
 * @created      30.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\MicrosoftGraph;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

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

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->userPrincipalName);
	}

}
