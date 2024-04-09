<?php
/**
 * Class PayPalTest
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\PayPal;
use function base64_encode;

/**
 * @property \chillerlan\OAuth\Providers\PayPal $provider
 */
final class PayPalTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return PayPal::class;
	}

}
