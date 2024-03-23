<?php
/**
 * @link https://dev.npr.org/api/?urls.primaryName=authorization#/authorization/getAuthorizationPage
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\NPROne;

$ENVVAR ??= 'NPRONE';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(NPROne::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
