<?php
/**
 * @link https://docs.gitlab.com/ee/api/oauth2.html
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\GitLab;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(GitLab::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
