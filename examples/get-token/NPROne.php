<?php
/**
 * @link https://dev.npr.org/api/?urls.primaryName=authorization#/authorization/getAuthorizationPage
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\NPROne;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(NPROne::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
