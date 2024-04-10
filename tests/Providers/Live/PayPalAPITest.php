<?php
/**
 * Class PayPalAPITest
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AuthenticatedUser;
use chillerlan\OAuth\Providers\PayPal;
use PHPUnit\Framework\Attributes\Group;
use function is_array;

/**
 * @property \chillerlan\OAuth\Providers\PayPal $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
final class PayPalAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return PayPal::class;
	}

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertSame($this->TEST_USER, $user->email);
	}

}
