<?php
/**
 * @link https://developers.bigcartel.com/api/v1#oauth-from-scratch
 *
 * @created      10.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\BigCartel;

$ENVVAR ??= 'BIGCARTEL';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(BigCartel::class, $ENVVAR);

/*
 * The BigCartel AccessToken instance holds additional values:
 *
 * $account_id = $token->extraParams['account_id'];
 */

require_once __DIR__.'/_flow-oauth2.php';

exit;
