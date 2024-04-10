<?php
/**
 * @link https://developers.bigcartel.com/api/v1#oauth-from-scratch
 *
 * @created      10.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\BigCartel;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(BigCartel::class);

/*
 * The BigCartel AccessToken instance holds additional values:
 *
 * $account_id = $token->extraParams['account_id'];
 */

require_once __DIR__.'/_flow-oauth2.php';

exit;
