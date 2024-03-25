<?php
/**
 * _flow-oauth1.php
 *
 * @created      04.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

/**
 * @var \chillerlan\OAuth\Core\OAuth1Interface $provider
 * @var array|null $PARAMS
 */

$name = $provider->serviceName;

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthURL($PARAMS));
}
// step 3: receive the access token
elseif(isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])){
	$token = $provider->getAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']);

	// save the token [...]

	// access granted, redirect
	header('Location: ?granted='.$name);
}
// step 4: verify the token and use the API
elseif(isset($_GET['granted']) && $_GET['granted'] === $name){
	$me        = print_r($provider->me(), true);
	$tokenJSON = $provider->getAccessTokenFromStorage()->toJSON();

	printf('<pre>%s</pre><textarea cols="120" rows="5" onclick="this.select();">%s</textarea>', $me, $tokenJSON);
}
// step 1 (optional): display a login link
else{
	echo '<a href="?login='.$name.'">connect with '.$name.'!</a>';
}
