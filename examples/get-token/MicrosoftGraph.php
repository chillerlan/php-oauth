<?php
/**
 * @link https://docs.microsoft.com/azure/active-directory/develop/v2-app-types
 *
 * @created      31.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\MicrosoftGraph;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(MicrosoftGraph::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
