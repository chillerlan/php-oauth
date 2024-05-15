# Create your own tests

Creating a test for a provider class is pretty straightforward: add a class to either the `Unit` or `Live` directory in `tests/Providers`,
extend one of the abstract test classes, add the attribute `chillerlan\OAuthTest\Attributes\Provider` with the FQCN of the test subject
as parameter to the test class, and you're ready!


## Unit test

The unit tests extend one of the abstract classes `OAuth1ProviderUnitTestAbstract`, `OAuth2ProviderUnitTestAbstract`
or in rare proprietary cases `OAuthProviderUnitTestAbstract`. There is no need to add extra tests for the several feature interfaces,
as these are already implemented in the abstract classes with `instanceof` checks. However, you may add tests for any custom functionality
of your provider class.

```php
namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\MyOAuth2Provider;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\MyOAuth2Provider $provider
 */
#[Provider(MyOAuth2Provider::class)]
final class MyOAuth2ProviderTest extends OAuth2ProviderUnitTestAbstract{

}
```


## Live test

The live tests are not only supposed to verify functionality against a live API, but also as a playground to try and figure out
API resources etc.

A live test class extends one of `OAuth1ProviderLiveTestAbstract`,  `OAuth2ProviderLiveTestAbstract` or `OAuthProviderLiveTestAbstract`.
In addition to the `Provider` attribute, it should also have PHPUnit's `Group` attribute set with the value `providerLiveTest`.

In order to verify the functionality of the `UserInfo::me()` method, the test method `OAuthProviderLiveTestAbstract::testMe()`
calls an environment variable `<prefix>_TESTUSER`, which is set in the (local) `.env` file. This variable is supposed to hold a
testable value (e.g. user handle, id, email...) returned by the API's user info endpoint, and that is accessible through a
`AuthenticatedUser` instance. The method `OAuthProviderLiveTestAbstract::assertMeResponse()` can be overridden to adjust the
asserted property (by default `AuthenticatedUser::$handle`).

```php
namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\MyOAuth2Provider;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property  \chillerlan\OAuth\Providers\MyOAuth2Provider $provider
 */
#[Group('providerLiveTest')]
#[Provider(MyOAuth2Provider::class)]
final class MyOAuth2ProviderAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function assertMeResponse(AuthenticatedUser $user):void{
		// testing against the email
		// in .env:
		// MYOAUTH2PROVIDER_TESTUSER=me@example.com
		$this::assertSame($this->TEST_USER, $user->email);
	}

	// or simply just skip it...
	public function testMe():void{
		$this::markTestSkipped("i don't care");
	}

}
```

### Import existing tokens

Ok, I'm being honest here: I've never bothered to think about a convenient way to import access tokens, because I use PHPStorm and
just drag the `.filestorage` directory from the *"Remote Host"* (where I run [the `get-token` examples](../Usage/Using-examples.md))
to the *"Project"* panel - done.

Anyway, as that's not feasible for everyone, here's a somewhat convenient solution: after access was granted in the `get-token` example,
there's a textarea input that holds the JSON representation of the `AccessToken` class, which you can use to import it back.
The JSON looks similar to the following:

```json
{
	"accessTokenSecret": null,
	"accessToken": "<access_token>",
	"refreshToken": null,
	"expires": -9002,
	"extraParams": {
		"token_type": "bearer"
	},
	"scopes": [
		"gist",
		"public_repo",
		"user"
	],
	"provider": "GitHub"
}
```

Copy the JSON from the textarea and save it in a file `<provider>.token.json`, here `GitHub.token.json`, the following script uses
the directory `<library root>/.config/tokens` to store the JSON tokens. Now create a `import-tokens.php` with the script below and run it:

```php
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\FileStorage;

// we're assuming the library root as location of this script
require_once __DIR__.'/vendor/autoload.php';

$options = new OAuthOptions;
// the storage path used in the testsuite
$options->fileStoragePath = __DIR__.'/.config/.filestorage';

// important: the $oauthUser parameter needs to be the same as in the testsuite
$storage = new FileStorage('oauth-example', $options);

// the directory wherever your JSON tokens are stored
foreach(new DirectoryIterator(__DIR__.'/.config/tokens') as $finfo){

	if(!str_contains($finfo->getFilename(), '.token.json')){
		continue;
	}

	$token = (new AccessToken)->fromJSON(file_get_contents($finfo->getLinkTarget()));

	$storage->storeAccessToken($token, $token->provider);
}
```
