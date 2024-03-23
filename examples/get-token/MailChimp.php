<?php
/**
 * @link http://developer.mailchimp.com/documentation/mailchimp/guides/how-to-use-oauth2/
 *
 * @created      16.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\MailChimp;

$ENVVAR ??= 'MAILCHIMP';

require_once __DIR__.'/../provider-example-common.php';

/**
 * @var \OAuthProviderFactory $factory
 * @var array|null $PARAMS
 * @var array|null $SCOPES
 */

$provider = $factory->getProvider(MailChimp::class, $ENVVAR);
$name     = $provider->serviceName;

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthURL($PARAMS, $SCOPES));
}
// step 3: receive the access token
elseif(isset($_GET['code']) && isset($_GET['state'])){
	$token = $provider->getAccessToken($_GET['code'], $_GET['state']);

	// MailChimp needs another call to the auth metadata endpoint
	// to receive the datacenter prefix/API URL, which will then
	// be stored in AccessToken::$extraParams
	$token = $provider->getTokenMetadata($token);

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
