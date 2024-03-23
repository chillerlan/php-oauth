<?php
/**
 * Class PayPalAPITest
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\PayPal;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;
use function is_array;

/**
 * @property \chillerlan\OAuth\Providers\PayPal $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
class PayPalAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return PayPal::class;
	}

	protected function getEnvPrefix():string{
		return 'PAYPAL'; // PAYPAL_SANDBOX
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{

		if(empty($json->emails) || !is_array($json->emails)){
			$this->markTestSkipped('no email found');
		}

		foreach($json->emails as $email){
			if($email->primary){
				$this::assertSame($this->TEST_USER, $email->value);
				return;
			}
		}

	}

}
