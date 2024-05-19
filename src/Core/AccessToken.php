<?php
/**
 * Class AccessToken
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @filesource
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\Settings\SettingsContainerAbstract;
use DateInterval, DateTime;
use function time;

/**
 * Access token implementation for any OAuth version.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5849#section-2.3
 * @link https://datatracker.ietf.org/doc/html/rfc6749#section-1.4
 */
final class AccessToken extends SettingsContainerAbstract{

	/**
	 * Denotes an unknown end of lifetime, such a token should be considered as expired.
	 *
	 * @var int
	 */
	public const EXPIRY_UNKNOWN = -0xDEAD;

	/**
	 * Denotes a token which never expires
	 *
	 * @var int
	 */
	public const NEVER_EXPIRES = -0xCAFE;

	/**
	 * Defines a maximum expiry period (1 year)
	 *
	 * @var int
	 */
	public const EXPIRY_MAX = (86400 * 365);

	/**
	 * (magic) The oauth access token
	 */
	protected string|null $accessToken = null;

	/**
	 * (magic) The access token secret (OAuth1)
	 */
	protected string|null $accessTokenSecret = null;

	/**
	 * (magic) An optional refresh token (OAuth2)
	 */
	protected string|null $refreshToken = null;

	/**
	 * (magic) The token expiration time
	 *
	 * The getter accepts: `DateTime|DateInterval|int|null`
	 */
	protected int $expires = self::EXPIRY_UNKNOWN;

	/**
	 * (magic) The scopes that are attached to this token
	 */
	protected array $scopes = [];

	/**
	 * (magic) Additional token parameters supplied by the provider
	 */
	protected array $extraParams = [];

	/**
	 * (magic) The provider that issued the token
	 */
	protected string|null $provider = null;

	/**
	 * Sets the expiration for this token, clamps the expiry to EXPIRY_MAX
	 */
	protected function set_expires(DateTime|DateInterval|int|null $expires = null):void{
		$now = time();
		$max = ($now + $this::EXPIRY_MAX);

		$this->expires = match(true){
			$expires instanceof DateTime                        => $expires->getTimeStamp(),
			$expires instanceof DateInterval                    => (new DateTime)->add($expires)->getTimeStamp(),
			$expires === 0 || $expires === $this::NEVER_EXPIRES => $this::NEVER_EXPIRES,
			$expires > $now                                     => $expires,
			$expires > 0 && $expires <= $this::EXPIRY_MAX       => ($now + $expires),
			default                                             => $this::EXPIRY_UNKNOWN,
		};

		// clamp max expiry
		if($this->expires > $max){
			$this->expires = $max;
		}

	}

	/**
	 * Checks whether this token is expired
	 */
	public function isExpired():bool{

		if($this->expires === $this::NEVER_EXPIRES){
			return false;
		}

		if($this->expires === $this::EXPIRY_UNKNOWN){
			return true;
		}

		return time() > $this->expires;
	}

}
