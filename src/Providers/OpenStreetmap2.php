<?php
/**
 * Class OpenStreetmap2
 *
 * @created      05.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{CSRFToken, OAuth2Provider};
use Psr\Http\Message\ResponseInterface;
use function sprintf, strip_tags;

/**
 * OpenStreetmap OAuth2
 *
 * @see https://wiki.openstreetmap.org/wiki/API
 * @see https://wiki.openstreetmap.org/wiki/OAuth
 * @see https://www.openstreetmap.org/.well-known/oauth-authorization-server
 */
class OpenStreetmap2 extends OAuth2Provider implements CSRFToken{

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
	 */
	public function me():ResponseInterface{
		$response = $this->request('/api/0.6/user/details.json');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$body = MessageUtil::getContents($response);

		if(!empty($body)){
			throw new ProviderException(strip_tags($body));
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
