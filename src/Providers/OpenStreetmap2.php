<?php
/**
 * Class OpenStreetmap2
 *
 * @created      05.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider, UserInfo};

/**
 * OpenStreetmap OAuth2
 *
 * @see https://wiki.openstreetmap.org/wiki/API
 * @see https://wiki.openstreetmap.org/wiki/OAuth
 * @see https://www.openstreetmap.org/.well-known/oauth-authorization-server
 */
class OpenStreetmap2 extends OAuth2Provider implements CSRFToken, UserInfo{

	public const SCOPE_READ_PREFS       = 'read_prefs';
	public const SCOPE_WRITE_PREFS      = 'write_prefs';
	public const SCOPE_WRITE_DIARY      = 'write_diary';
	public const SCOPE_WRITE_API        = 'write_api';
	public const SCOPE_READ_GPX         = 'read_gpx';
	public const SCOPE_WRITE_GPX        = 'write_gpx';
	public const SCOPE_WRITE_NOTES      = 'write_notes';
#	public const SCOPE_READ_EMAIL       = 'read_email';
#	public const SCOPE_SKIP_AUTH        = 'skip_authorization';
	public const SCOPE_WRITE_REDACTIONS = 'write_redactions';
	public const SCOPE_OPENID           = 'openid';

	public const DEFAULT_SCOPES = [
		self::SCOPE_READ_GPX,
		self::SCOPE_READ_PREFS,
	];

	protected string      $authURL        = 'https://www.openstreetmap.org/oauth2/authorize';
	protected string      $accessTokenURL = 'https://www.openstreetmap.org/oauth2/token';
#	protected string      $revokeURL      = 'https://www.openstreetmap.org/oauth2/revoke'; // not implemented yet?
	protected string      $apiURL         = 'https://api.openstreetmap.org';
	protected string|null $apiDocs        = 'https://wiki.openstreetmap.org/wiki/API';
	protected string|null $applicationURL = 'https://www.openstreetmap.org/oauth2/applications';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/api/0.6/user/details.json');

		$userdata = [
			'data'        => $json,
			'avatar'      => $json['user']['img']['href'],
			'displayName' => $json['user']['display_name'],
			'id'          => $json['user']['id'],
		];

		return new AuthenticatedUser($userdata);
	}

}
