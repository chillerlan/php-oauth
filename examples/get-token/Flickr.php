<?php
/**
 * @link https://www.flickr.com/services/api/auth.oauth.html
 *
 * @created      20.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\Flickr;

$ENVVAR ??= 'FLICKR';
$PARAMS ??= ['perms' => Flickr::PERM_DELETE];

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(Flickr::class, $ENVVAR);

/*
 * The Flickr AccessToken instance holds additional values:
 *
 * $user_name = $token->extraParams['username'];
 * $user_id   = $token->extraParams['user_nsid'];
 */

require_once __DIR__.'/_flow-oauth1.php';

exit;
