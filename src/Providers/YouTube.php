<?php
/**
 * Class YouTube
 *
 * @created      09.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

/**
 * YouTube OAuth2
 *
 * @see https://developers.google.com/youtube
 */
class YouTube extends Google{

	public const IDENTIFIER = 'YOUTUBE';

	public const SCOPE_YOUTUBE       = 'https://www.googleapis.com/auth/youtube';
	public const SCOPE_YOUTUBE_GDATA = 'https://gdata.youtube.com';

	public const DEFAULT_SCOPES = [
		self::SCOPE_EMAIL,
		self::SCOPE_PROFILE,
		self::SCOPE_YOUTUBE,
		self::SCOPE_YOUTUBE_GDATA,
	];

}
