<?php
/**
 * @link https://developer.twitter.com/en/docs/basics/authentication/overview/oauth
 * @link https://developer.twitter.com/en/docs/basics/authentication/overview/application-only
 *
 * @created      10.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Twitter;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Twitter::class);

/*
 * The Twitter AccessToken instance holds additional values:
 *
 * $screen_name = $token->extraParams['screen_name'];
 * $user_id     = $token->extraParams['user_id'];
 */

require_once __DIR__.'/_flow-oauth1.php';

exit;
