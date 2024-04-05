<?php
/**
 * @link https://apidocs.imgur.com/?version=latest#authorization-and-oauth
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Imgur;

$ENVVAR ??= 'IMGUR';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Imgur::class, $ENVVAR);

/*
 * The Imgur AccessToken instance holds additional values:
 *
 * $username = $token->extraParams['account_username'];
 * $id       = $token->extraParams['account_id'];
 */

require_once __DIR__.'/_flow-oauth2.php';

exit;
