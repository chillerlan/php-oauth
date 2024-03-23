<?php
/**
 * @link https://docs.gitlab.com/ee/api/oauth2.html
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\GitLab;

$ENVVAR ??= 'GITLAB';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(GitLab::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
