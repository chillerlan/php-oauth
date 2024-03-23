<?php
/**
 * Class AzureActiveDirectory
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{CSRFToken, OAuth2Provider};

/**
 * Microsoft identity platform (OAuth2)
 *
 * @see https://learn.microsoft.com/en-us/entra/identity-platform/v2-app-types
 * @see https://learn.microsoft.com/en-us/entra/identity-platform/v2-oauth2-auth-code-flow
 * @see https://learn.microsoft.com/en-us/entra/identity-platform/v2-oauth2-client-creds-grant-flow
 */
abstract class AzureActiveDirectory extends OAuth2Provider implements CSRFToken{

	public const SCOPE_OPENID         = 'openid';
	public const SCOPE_OPENID_EMAIL   = 'email';
	public const SCOPE_OPENID_PROFILE = 'profile';
	public const SCOPE_OFFLINE_ACCESS = 'offline_access';

	protected string      $authURL        = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
	protected string      $accessTokenURL = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
	protected string|null $userRevokeURL  = 'https://account.live.com/consent/Manage';
	protected string|null $applicationURL = 'https://aad.portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/RegisteredApps';

}
