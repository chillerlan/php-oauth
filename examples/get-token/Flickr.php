<?php
/**
 * @link https://www.flickr.com/services/api/auth.oauth.html
 *
 * @created      20.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Flickr;

$PARAMS ??= ['perms' => 'read']; // hen-egg issue: can't use the Flickr class before it's loaded

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Flickr::class);

/*
 * The Flickr AccessToken instance holds additional values:
 *
 * $user_name = $token->extraParams['username'];
 * $user_id   = $token->extraParams['user_nsid'];
 */

require_once __DIR__.'/_flow-oauth1.php';

exit;
