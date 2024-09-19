<?php
/**
 * Class DeviantArt
 *
 * @created      26.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{
	AccessToken, AuthenticatedUser, ClientCredentials, ClientCredentialsTrait,
	CSRFToken, OAuth2Provider, TokenInvalidate, TokenRefresh, UserInfo,
};
use chillerlan\OAuth\Storage\MemoryStorage;
use Throwable;
use function sprintf;

/**
 * DeviantArt OAuth2
 *
 * @link https://www.deviantart.com/developers/
 */
class DeviantArt extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenInvalidate, TokenRefresh, UserInfo{
	use ClientCredentialsTrait;

	public const IDENTIFIER = 'DEVIANTART';

	public const SCOPE_BASIC        = 'basic';
	public const SCOPE_BROWSE       = 'browse';
	public const SCOPE_COLLECTION   = 'collection';
	public const SCOPE_COMMENT_POST = 'comment.post';
	public const SCOPE_FEED         = 'feed';
	public const SCOPE_GALLERY      = 'gallery';
	public const SCOPE_MESSAGE      = 'message';
	public const SCOPE_NOTE         = 'note';
	public const SCOPE_STASH        = 'stash';
	public const SCOPE_USER         = 'user';
	public const SCOPE_USER_MANAGE  = 'user.manage';

	public const DEFAULT_SCOPES = [
		self::SCOPE_BASIC,
		self::SCOPE_BROWSE,
	];

	public const HEADERS_API = [
		'dA-minor-version' => '20210526',
	];

	protected string      $authorizationURL = 'https://www.deviantart.com/oauth2/authorize';
	protected string      $accessTokenURL   = 'https://www.deviantart.com/oauth2/token';
	protected string      $revokeURL        = 'https://www.deviantart.com/oauth2/revoke';
	protected string      $apiURL           = 'https://www.deviantart.com/api/v1/oauth2';
	protected string|null $userRevokeURL    = 'https://www.deviantart.com/settings/applications';
	protected string|null $apiDocs          = 'https://www.deviantart.com/developers/';
	protected string|null $applicationURL   = 'https://www.deviantart.com/developers/apps';

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/user/whoami');

		$userdata = [
			'data'   => $json,
			'avatar' => $json['usericon'],
			'handle' => $json['username'],
			'id'     => $json['userid'],
			'url'    => sprintf('https://www.deviantart.com/%s', $json['username']),
		];

		return new AuthenticatedUser($userdata);
	}

	public function invalidateAccessToken(AccessToken|null $token = null, string|null $type = null):bool{

		if($token !== null){
			// to revoke a token different from the one of the currently authenticated user,
			// we're going to clone the provider and feed the other token for the invalidate request
			return (clone $this)
				->setStorage(new MemoryStorage)
				->storeAccessToken($token)
				->invalidateAccessToken()
			;
		}

		$request  = $this->requestFactory->createRequest('POST', $this->revokeURL);
		$response = $this->http->sendRequest($this->getRequestAuthorization($request));

		try{
			$json = MessageUtil::decodeJSON($response);
		}
		catch(Throwable){
			return false;
		}

		if($response->getStatusCode() === 200 && !empty($json->success)){
			// delete the token from storage
			$this->storage->clearAccessToken($this->name);

			return true;
		}

		return false;
	}

}
