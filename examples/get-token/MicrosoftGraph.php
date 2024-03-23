<?php
/**
 * @link https://docs.microsoft.com/azure/active-directory/develop/v2-app-types
 *
 * @created      31.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\MicrosoftGraph;

$ENVVAR ??= 'MICROSOFT_AAD';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(MicrosoftGraph::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
