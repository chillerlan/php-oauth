<?php
/**
 * @link https://musicbrainz.org/doc/Development/OAuth2
 *
 * @created      31.07.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\MusicBrainz;

$ENVVAR ??= 'MUSICBRAINZ';
$PARAMS ??= [
	'access_type'     => 'offline',
	'approval_prompt' => 'force',
];

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(MusicBrainz::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
