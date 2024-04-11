<?php
/**
 * Class AccessToken
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\Settings\SettingsContainerAbstract;
use DateInterval, DateTime;
use function time;

/**
 * Access token implementation for any OAuth version.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc5849#section-2.3
 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-1.4
 *
 * // Oauth1
 * @property string|null                    $accessTokenSecret
 *
 * // Oauth2
 * @property array                          $scopes
 * @property string|null                    $refreshToken
 *
 * // common
 * @property string|null                    $accessToken
 * @property DateTime|DateInterval|int|null $expires
 * @property array                          $extraParams
 * @property string                         $provider
 */
final class AccessToken extends SettingsContainerAbstract{

	/**
	 * Denotes an unknown end of lifetime, such a token should be considered as expired.
	 */
	public const EXPIRY_UNKNOWN = -9001;

	/**
	 * Denotes a token which never expires
	 */
	public const NEVER_EXPIRES = -9002;

	/**
	 * Defines a maximum expiry period (1 year)
	 */
	public const EXPIRY_MAX = (86400 * 365);

	/**
	 * The access token secret (OAuth1)
	 */
	protected string|null $accessTokenSecret = null;

	/**
	 * The oauth access token
	 */
	protected string|null $accessToken = null;

	/**
	 * An optional refresh token (OAuth2)
	 */
	protected string|null $refreshToken = null;

	/**
	 * The token expiration date/time
	 */
	protected int $expires = self::EXPIRY_UNKNOWN;

	/**
	 * Additional token parameters supplied by the provider
	 */
	protected array $extraParams = [];

	/**
	 * The scopes that are attached to this token (OAuth2)
	 */
	protected array $scopes = [];

	/**
	 * The provider who issued this token
	 */
	protected string|null $provider = null;

	/**
	 * Expiry setter
	 */
	protected function set_expires(DateTime|DateInterval|int|null $expires = null):void{
		$this->setExpiry($expires);
	}

	/**
	 * Sets the expiration for this token, clamps the expiry to EXPIRY_MAX
	 */
	public function setExpiry(DateTime|DateInterval|int|null $expires = null):AccessToken{
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

		return $this;
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
