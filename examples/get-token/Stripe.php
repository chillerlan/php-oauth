<?php
/**
 * @link https://stripe.com/docs/connect/authentication
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\Stripe;

$ENVVAR ??= 'STRIPE';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(Stripe::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
