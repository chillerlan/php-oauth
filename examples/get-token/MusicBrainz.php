<?php
/**
 * @link https://musicbrainz.org/doc/Development/OAuth2
 *
 * @created      31.07.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\MusicBrainz;

$ENVVAR ??= 'MUSICBRAINZ';
$PARAMS ??= [
	'access_type'     => 'offline',
	'approval_prompt' => 'force',
	'state'           => sha1(random_bytes(256)),
];

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(MusicBrainz::class, $ENVVAR);
$name     = $provider->serviceName;

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthURL($PARAMS));
}
// step 3: receive the access token
elseif(isset($_GET['code']) && isset($_GET['state'])){
	$token = $provider->getAccessToken($_GET['code'], $_GET['state']);

	// save the token [...]

	// access granted, redirect
	header('Location: ?granted='.$name);
}
// step 4: verify the token and use the API
elseif(isset($_GET['granted']) && $_GET['granted'] === $name){
	$response = $provider->request(sprintf('/artist/%s', '573510d6-bb5d-4d07-b0aa-ea6afe39e28d'), ['inc' => 'url-rels work-rels']);

	echo '<pre>'.print_r(MessageUtil::decodeJSON($response), true).'</pre>'.
	     '<textarea cols="120" rows="3" onclick="this.select();">'.
	     $provider->getAccessTokenFromStorage()->toJSON().
	     '</textarea>';
}
// step 1 (optional): display a login link
else{
	echo '<a href="?login='.$name.'">connect with '.$name.'!</a>';
}

exit;
