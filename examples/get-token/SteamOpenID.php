<?php
/**
 * @link https://steamcommunity.com/dev
 *
 * @created      20.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\SteamOpenID;

$ENVVAR ??= 'STEAMOPENID';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(SteamOpenID::class, $ENVVAR);
$name     = $provider->serviceName;

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthURL());
}
// step 3: receive the access token
elseif(isset($_GET['openid_sig']) && isset($_GET['openid_signed'])){
	$token = $provider->getAccessToken($_GET);

	// save the token [...]

	// access granted, redirect
	header('Location: ?granted='.$name);
}
//step 3.1: oh noes!
elseif(isset($_GET['openid_error'])){ // openid.error -> https://stackoverflow.com/questions/68651/
	exit('oh noes: '.$_GET['openid_error']);
}
// step 4: verify the token and use the API
elseif(isset($_GET['granted']) && $_GET['granted'] === $name){
	$token    = $provider->getAccessTokenFromStorage(); // the user's steamid is stored as access token
	$response = $provider->request('/ISteamUser/GetPlayerSummaries/v2', ['steamids' => $token->accessToken]);

	echo '<pre>'.print_r(MessageUtil::decodeJSON($response), true).'</pre>'.
	     '<textarea cols="120" rows="3" onclick="this.select();">'
	     .$token->toJSON().
	     '</textarea>';
}
// step 1 (optional): display a login link
else{
	echo '<a href="?login='.$name.'">connect with '.$name.'!</a>';
}
