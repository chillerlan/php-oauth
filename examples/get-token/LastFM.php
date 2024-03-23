<?php
/**
 * @link https://www.last.fm/api/authentication
 *
 * @created      10.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\LastFM;

$ENVVAR ??= 'LASTFM';

require_once __DIR__.'/../provider-example-common.php';

/**
 * @var \OAuthProviderFactory $factory
 * @var array|null $PARAMS
 */

$provider = $factory->getProvider(LastFM::class, $ENVVAR);
$name     = $provider->serviceName;

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthURL($PARAMS));
}
// step 3: receive the access token
elseif(isset($_GET['token'])){
	$token = $provider->getAccessToken($_GET['token']);

	// save the token [...]

	// access granted, redirect
	header('Location: ?granted='.$name);
}
// step 4: verify the token and use the API
elseif(isset($_GET['granted']) && $_GET['granted'] === $name){
	echo '<pre>'.print_r(MessageUtil::decodeJSON($provider->me()), true).'</pre>'.
	     '<textarea cols="120" rows="3" onclick="this.select();">'.
	     $provider->getAccessTokenFromStorage()->toJSON().
	     '</textarea>';
}
// step 1 (optional): display a login link
else{
	echo '<a href="?login='.$name.'">connect with '.$name.'!</a>';
}

exit;
