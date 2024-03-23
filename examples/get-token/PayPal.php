<?php
/**
 * @link https://developer.paypal.com/docs/connect-with-paypal/integrate/
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\PayPal;

$ENVVAR ??= 'PAYPAL'; // PAYPAL_SANDBOX
$PARAMS ??= ['flowEntry' => 'static', 'fullPage' => 'true'];

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(PayPal::class, $ENVVAR); // PayPalSandbox

require_once __DIR__.'/_flow-oauth2.php';

exit;
