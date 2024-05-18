# Authorization flow

## Redirect the user to the provider

The first step is to redirect the user to the provider's log-in page. Ideally you'd do this with a separate route in your web application,
as provider instances may call the service internally (OAuth1 request tokens). This step is identical with all providers.


### Present a log-in link

First you present a log-in link to the user - instead of directly embedding/linking the one-time authorization URL, you redirect them to a separate route:

```php
// display a link to the log-in redirect
echo '<a href="?route=oauth-login">connect with GitHub!</a>';
```


### Redirect

The method `OAuthInterface::getAuthorizationURL()` takes two (optional) parameters:

- `$params`: this array contains additional query parameters that will be added to the URL query (service dependent)
- `$scopes`: this array contains all scopes that will be used for this authorization

When the user clicks the log-in link, just execute a `header()` to the provider's authorization URL.

```php
if($route === 'oauth-login'){
	header('Location: '.$provider->getAuthorizationURL($params, $scopes));

	// -> https://github.com/login/oauth/authorize?client_id=<client_id>
	//       &redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2F&response_type=code
	//       &scope=<scopes>&state=<state>&type=web_server
}
```


## Receive the incoming callback

After a user has successfully logged in with the provider and confirmed the authorization request, they are redirected to the given callback URL,
along with the query parameters `oauth_token` and `oauth_verifier` (OAuth1) or `code` and `state` (OAuth2).

The method `OAuth1Interface::getAccessToken()` has 2 mandatory parameters `$requestToken` and `$verifier`,
while the similar `OAuth2Interface::getAccessToken()` takes two parameters `$code` and the (optional) `$state`.


### OAuth2

In our GitHub OAuth2 example we're now receiving the incoming callback to `https://example.com/oauth/?code=<code>&state=<state>`.
The `getAccessToken()` method initiates a backend request to the provider's server to exchange the temporary credentials for an access token:

```php
if($route === 'oauth2-callback'){

	if(isset($_GET['code'])){
		$state = null;

		// not all providers support "state"
		if($provider instanceof CSRFToken && isset($_GET['state'])){
			$state = $_GET['state'];
		}

		$token = $provider->getAccessToken($_GET['code'], $state);

		// when everything is done here, you should redirect the user to wherever
		// they were headed, but also to clear the URL query parameters
		header('Location: ...');
	}

	// handle error...
}
```


### OAuth1

For OAuth1 providers it looks very similar, only the URL query parameters are different:

```php
if($route === 'oauth1-callback'){

	if(isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])){

		$token = $provider->getAccessToken(
			$_GET['oauth_token'],
			$_GET['oauth_verifier'],
		);

		// ...

		header('Location: ...');
	}

}
```

Some services may send additional data with the callback query parameters, that you might want to save along with the token after calling `getAccessToken()` -
alternatively you can override this method to add functionality.


## Use the provider's API

After exchanging and saving the access token, you're now ready to access the provider's API on behalf of the authenticated user.
Most providers offer a token verification, user or just "me" endpoint, that you can access conveniently via the `OAuthInterface::me()` method:

```php
$user = $provider->me(); // -> AuthenticatedUser instance
```

Every provider instance also acts as PSR-18 client: calls to the provider's API will have the `Authorization` headers added automatically,
calls to other hosts will be rerouted directly to the http client.

```php
$request  = $requestFactory->createRequest('GET', 'https://api.github.com/user');
// authenticated request
$response = $provider->sendRequest($request); // -> ResponseInterface instance
```

You might want to wrap the API call in a try/catch block that catches the `InvalidAccessTokenException` (expired token, unable to refresh)
or its parent `UnauthorizedAccessException` (general HTTP error: 400, 401, 403) and decide what to do in that case:

```php
try{
	$response = $provider->sendRequest($request);
}
catch(Throwable $e){

	if($e instanceof InvalidAccessTokenException){
		// redirect to (re-) authorization
		header('Location: '.$provider->getAuthorizationURL($params, $scopes));
	}
	elseif($e instanceof UnauthorizedAccessException){
		// handle http error
	}

	// something else went horribly wrong
	throw $e;
}
```
