<?php
/**
 * Class MailChimpAPITest
 *
 * @created      16.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\MailChimp;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\MailChimp $provider
 */
#[Group('providerLiveTest')]
class MailChimpAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return MailChimp::class;
	}

	protected function getEnvPrefix():string{
		return 'MAILCHIMP';
	}

	public function testGetTokenMetadata():void{
		$token = $this->storage->getAccessToken($this->provider->serviceName);
		$token = $this->provider->getTokenMetadata($token);

		$this::assertSame($this->TEST_USER, $token->extraParams['accountname']);
	}

}
