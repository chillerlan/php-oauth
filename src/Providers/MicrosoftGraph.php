<?php
/**
 * Class MicrosoftGraph
 *
 * @created      30.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use Psr\Http\Message\ResponseInterface;
use function sprintf;

/**
 * Microsoft Graph OAuth2
 *
 * @see https://learn.microsoft.com/en-us/graph/permissions-reference
 */
class MicrosoftGraph extends AzureActiveDirectory{

	public const SCOPE_USER_READ          = 'User.Read';
	public const SCOPE_USER_READBASIC_ALL = 'User.ReadBasic.All';

	public const DEFAULT_SCOPES = [
		self::SCOPE_OPENID,
		self::SCOPE_OPENID_EMAIL,
		self::SCOPE_OPENID_PROFILE,
		self::SCOPE_OFFLINE_ACCESS,
		self::SCOPE_USER_READ,
		self::SCOPE_USER_READBASIC_ALL,
	];

	protected string      $apiURL  = 'https://graph.microsoft.com';
	protected string|null $apiDocs = 'https://learn.microsoft.com/graph/overview';

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/v1.0/me');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->error, $json->error->message)){
			throw new ProviderException($json->error->message);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
