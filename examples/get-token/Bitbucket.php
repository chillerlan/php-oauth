<?php
/**
 * @link https://developer.atlassian.com/cloud/bitbucket/oauth-2/
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Bitbucket;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Bitbucket::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
