<?php
/**
 * @link https://steamcommunity.com/dev
 *
 * @created      20.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\SteamOpenID;

$ENVVAR ??= 'STEAMOPENID';

require_once __DIR__.'/../provider-example-common.php';

/**
 * @var \OAuthExampleProviderFactory            $factory
 * @var \chillerlan\OAuth\Providers\SteamOpenID $provider
 */
$provider = $factory->getProvider(SteamOpenID::class, $ENVVAR);
$name     = $provider->name;

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthURL());
}
// step 3: receive the access token
elseif(isset($_GET['openid_sig']) && isset($_GET['openid_signed'])){
	// the SteamOpenID provider takes the whole $_GET array as it uses multiple of the query parameters
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
	$token     = $provider->getAccessTokenFromStorage(); // the user's steamid is stored as access token
	$response  = $provider->request('/ISteamUser/GetPlayerSummaries/v2', ['steamids' => $token->accessToken]);
	$data      = print_r(MessageUtil::decodeJSON($response), true);
	$tokenJSON = $token->toJSON();

	printf('<pre>%s</pre><textarea cols="120" rows="5" onclick="this.select();">%s</textarea>', $data, $tokenJSON);
}
// step 1 (optional): display a login link
else{
	echo '<a href="?login='.$name.'">connect with '.$name.'!</a>';
}
