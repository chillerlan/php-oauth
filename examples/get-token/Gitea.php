<?php
/**
 * @link https://docs.gitea.com/development/oauth2-provider
 *
 * @created      08.04.2024
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Gitea;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Gitea::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
