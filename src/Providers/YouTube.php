<?php
/**
 * Class YouTube
 *
 * @created      09.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

/**
 * YouTube OAuth2
 *
 * @see https://developers.google.com/youtube
 */
class YouTube extends Google{

	public const SCOPE_YOUTUBE       = 'https://www.googleapis.com/auth/youtube';
	public const SCOPE_YOUTUBE_GDATA = 'https://gdata.youtube.com';

}
