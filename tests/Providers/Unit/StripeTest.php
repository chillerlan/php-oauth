<?php
/**
 * Class StripeTest
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Stripe;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Stripe $provider
 */
#[Provider(Stripe::class)]
final class StripeTest extends OAuth2ProviderUnitTestAbstract{

	public function testTokenInvalidate():void{
		$this::markTestIncomplete();
	}

}
