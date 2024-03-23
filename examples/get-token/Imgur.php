<?php
/**
 * @link https://apidocs.imgur.com/?version=latest#authorization-and-oauth
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Imgur;

$ENVVAR ??= 'IMGUR';

require_once __DIR__.'/../provider-example-common.php';

/**
 * @var \OAuthProviderFactory $factory
 * @var array|null $PARAMS
 * @var array|null $SCOPES
 */

$provider = $factory->getProvider(Imgur::class, $ENVVAR);
$storage  = $provider->getStorage();
$name     = $provider->serviceName;

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthURL($PARAMS, $SCOPES));
}
// step 3: receive the access token
elseif(isset($_GET['code']) && isset($_GET['state'])){
	$token = $provider->getAccessToken($_GET['code'], $_GET['state']);

	$username = $token->extraParams['account_username'];
	$id       = $token->extraParams['account_id'];

	// set the expiry to a sane period
	$token->expires = (time() + 2592000); // 30 days
	// save the token [...]
	$storage->storeAccessToken($token);

	// access granted, redirect
	header('Location: ?granted='.$name);
}
// step 4: verify the token and use the API
elseif(isset($_GET['granted']) && $_GET['granted'] === $name){
	echo '<pre>'.print_r(MessageUtil::decodeJSON($provider->me()), true).'</pre>'.
	     '<textarea cols="120" rows="3" onclick="this.select();">'.
	     $storage->getAccessToken($name)->toJSON().
	     '</textarea>';
}
// step 1 (optional): display a login link
else{
	echo '<a href="?login='.$name.'">connect with '.$name.'!</a>';
}

exit;
