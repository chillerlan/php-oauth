<?php
/**
 * @filesource   lastfm-common.php
 * @created      03.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\LastFM;

$ENVVAR = 'LASTFM';

require_once __DIR__.'/../../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$lfm = $factory->getProvider(LastFM::class, $ENVVAR);
