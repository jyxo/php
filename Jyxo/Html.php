<?php declare(strict_types = 1);

/**
 * Jyxo PHP Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/jyxo/php/blob/master/license.txt
 */

namespace Jyxo;

/**
 * Functions for HTML processing.
 *
 * @category Jyxo
 * @package Jyxo\Html
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Html
{
	/**
	 * Constructor preventing from creating instances of a static class.
	 *
	 * @throws \LogicException If trying to create an instance
	 */
	public final function __construct()
	{
		throw new \LogicException(sprintf('Cannot create an instance of a static class %s.', get_class($this)));
	}

	/**
	 * Tests if the given text contains at least one HTML tag.
	 * It is just an estimation.
	 *
	 * @param string $text Input text to be tested
	 * @return boolean
	 */
	public static function is(string $text): bool
	{
		return (bool) preg_match('~<[a-z][a-z0-9]*(\\s[^<]*)?>~i', $text);
	}

	/**
	 * Fixes an invalid HTML source, unifies quotes and removes unnecessary whitespace.
	 * Required the Tidy PHP extension.
	 *
	 * @param string $html Input HTML source
	 * @return string
	 */
	public static function repair(string $html): string
	{
		// HTML fixing
		static $config = [
			'newline' => 'LF',				// Uses LF line endings
			'indent' => false,				// Removes indent
			'output-xhtml' => true,			// Output will be in XHTML format
			'output-bom' => false,			// No BOM
			'doctype' => 'auto',			// Automatic doctype
			// 'clean' => true,				// Removes presentation tags (inline styles would be moved into <style> elements)
			'bare' => true,					// Cleans MS HTML mess
			'wrap' => 0,					// No wrapping
			'wrap-sections' => false,		// No <![ ... ]> wrapping
			// 'quote-marks' => true,		// Replaces quotes with appropriate entities (causes problems with later regular expression processing)
			// 'logical-emphasis' => true,	// Replaces all <i> and <b> tags with <em> and <strong> (styles cannot be parsed after)
			'enclose-text' => true,			// Text inside <body> encapsulates with a <p> tag
			'merge-divs' => false,			// Disables <div> merging
			'merge-spans' => false,			// Disables <span> merging
			// 'hide-comments' => true,		// Removes comments (it would remove conditional comments used when inserting Flash)
			'force-output' => true,			// Makes output even on error
			'show-errors' => 0,				// Don't show any errors
			'show-warnings' => false,		// Don't show any warnings
			'escape-cdata' => true,			// Makes an ordinary text from CDATA blocks
			'preserve-entities' => true		// Preserves correctly formatted entities
			// 'drop-proprietary-attributes' => true,	// Removes proprietary attributes (it would remove e.g. the background attribute)
			// 'drop-font-tags' => true		// Removes <FONT> and <CENTER> tags
		];

		if (!function_exists('\tidy_repair_string')) {
			throw new \Jyxo\Exception('Missing "tidy" extension.');
		}

		$html = tidy_repair_string($html, $config, 'utf8');

		// Removes namespace <?xml:namespace prefix = o ns = "urn:schemas-microsoft-com:office:office" /? > generated by MS Word
		$html = preg_replace('~<\?xml:namespace[^>]*>~i', '', $html);

		// Removes unnecessary line breaks and keeps them inside <pre> elements
		// Tidy adds one more line breaks inside <pre> elements
		$html = preg_replace("~(<pre[^>]*>)\n~", '\\1', $html);
		$html = preg_replace("~\n</pre>~", '</pre>', $html);
		$html = preg_replace_callback('~(<pre[^>]*>)(.+?)(</pre>)~s', function($matches) {
			return $matches[1] . strtr(nl2br($matches[2]), ['\"' => '"']) . $matches[3];
		}, $html);
		// Strip line breaks
		$html = strtr($html, ["\r" => '', "\n" => '']);

		// Replace single quotes with double quotes (for easier processing later)
		$html = preg_replace('~(<[a-z][a-z0-9]*[^>]+[a-z]+=)\'([^\']*)\'~i', '\\1"\\2"', $html);

		// Remove unnecessary spaces inside elements (for easier processing later)
		$html = preg_replace('~(<[a-z][a-z0-9]*[^>]+[a-z]+=")\\s+([^"]*")~i', '\\1\\2', $html);
		$html = preg_replace('~(<[a-z][a-z0-9]*[^>]+[a-z]+="[^"]*)\s+(")~i', '\\1\\2', $html);

		return $html;
	}

	/**
	 * Removes given tags from the HTML source.
	 * If no tags are given, the default set is used.
	 * Expects valid HTML code.
	 *
	 * @param string $html HTML source code
	 * @param array $tags Tags to be removed
	 * @return string
	 */
	public static function removeTags(string $html, array $tags = []): string
	{
		// Default set of tags
		static $default = [
			'frameset', 'frame', 'noframes', 'iframe', 'script', 'noscript', 'style', 'link',
			'object', 'embed', 'form', 'input', 'select', 'textarea', 'button'
		];

		// If no tags are set, the default set will be used
		if (empty($tags)) {
			$tags = $default;
		}

		// Remove given tags
		foreach ($tags as $tag) {
			switch ($tag) {
				// Embed
				case 'embed':
					// Second variant is because of Tidy that processes <embed> this way
					$pattern = ['~\s*<embed[^>]*>.*?</embed>~is', '~\s*<embed[^>]*>~is'];
					break;
				// Self closing tags
				case 'link':
				case 'meta':
				case 'br':
				case 'hr':
				case 'img':
				case 'input':
					$pattern = ['~\s*<' . $tag . '[^>]*>~is'];
					break;
				// Pair tags
				default:
					$pattern = ['~\s*<' . $tag . '(?:\s+[^>]*)?>.*?</' . $tag . '>~is'];
					break;
			}

			$html = preg_replace($pattern, '', $html);
		}

		return $html;
	}

	/**
	 * Removes tags of the same type nested into each other from the HTML source.
	 * Expects valid HTML source
	 *
	 * @param string $html HTML source code
	 * @param string $tag Tags to be processed
	 * @return string
	 */
	public static function removeInnerTags(string $html, string $tag): string
	{
		if (preg_match_all('~(?:<' . $tag . '>)|(?:</' . $tag . '>)|(?:<[^>]+>)|(?:[^<]+)~i', $html, $matches)) {
			$html = '';
			$level = 0;
			foreach ($matches[0] as $htmlPart) {
				if (0 === stripos($htmlPart, '<' . $tag)) {
					$level++;
					if (1 === $level) {
						$html .= $htmlPart;
					}
				} elseif (0 === stripos($htmlPart, '</' . $tag)) {
					if (1 === $level) {
						$html .= $htmlPart;
					}
					$level--;
				} else {
					$html .= $htmlPart;
				}
			}
		}

		return $html;
	}

	/**
	 * Removes given attributes from the HTML source.
	 * If no attributes are given, the default set will be used.
	 * Expects valid HTML source.
	 *
	 * @param string $html HTML source code
	 * @param array $attributes Attributes to be removed
	 * @return string
	 */
	public static function removeAttributes(string $html, array $attributes = []): string
	{
		// Default set of attributes
		static $default = ['id', 'class'];

		// If no attributes are given, the default set will be used
		if (empty($attributes)) {
			$attributes = $default;
		}

		// Remove given attributes
		foreach ($attributes as $attribute) {
			$html = preg_replace('~(<[a-z][a-z0-9]*[^>]*?)\\s+' . $attribute . '="[^"]*"~is', '\\1', $html);
		}

		return $html;
	}

	/**
	 * Removes all javascript events from the HTML source.
	 * If it is necessary to remove only certain events, the removeAttributes() method can be used.
	 * Expects valid HTML source.
	 *
	 * @param string $html HTML source code
	 * @return string
	 */
	public static function removeJavascriptEvents(string $html): string
	{
		// A tag can have multiple events, therefore it is necessary to process the source multiple times
		while (preg_match('~<[a-z][a-z0-9]*[^>]*?\\s+on[a-z]+="[^"]*"~is', $html)) {
			$html = preg_replace('~(<[a-z][a-z0-9]*[^>]*?)\\s+on[a-z]+="[^"]*"~is', '\\1', $html);
		}
		return $html;
	}

	/**
	 * Removes foreign images from the HTML source.
	 * Keeps <img> tags (only set the value about:blank into its src attribute), because removing the tag entirely could affect
	 * the page layout.
	 * Expects valid HTML source.
	 *
	 * @param string $html HTML source code
	 * @return string
	 */
	public static function removeRemoteImages(string $html): string
	{
		static $remoteImages = [
			'~(<img[^>]+src=")http(?:s)?://[^"]+(")~i',
			'~(<[a-z][a-z0-9]*[^>]+background=")http(?:s)?://[^"]+(")~i',
			'~(<[a-z][a-z0-9]*[^>]+style="[^"]*background\\s*[:])([\-a-z0-9#%\\s]*)url\([^)]+\)(;)?~is',
			'~(<[a-z][a-z0-9]*[^>]+style="[^"]*)background-image\\s*[:]([\-a-z0-9#%\\s]*)url\([^)]+\)(;)?~is',
			'~(<[a-z][a-z0-9]*[^>]+style="[^"]*list-style\\s*[:])([\-a-z0-9\\s]*)url\([^)]+\)(;)?~is',
			'~(<[a-z][a-z0-9]*[^>]+style="[^"]*)list-style-image\\s*[:]([\-a-z0-9\\s]*)url\([^)]+\)(;)?~is'
		];
		// We use value about:blank for the <img> tag's src attribute, because removing the tag entirely could affect the page layout
		static $remoteImagesReplacement = [
			'\\1about:blank\\2',
			'\\1\\2',
			'\\1\\2\\3',
			'\\1',
			'\\1\\2\\3',
			'\\1'
		];

		return preg_replace($remoteImages, $remoteImagesReplacement, $html);
	}

	/**
	 * Removes possibly dangerous attributes that could contain XSS code from the HTML source.
	 *
	 * @param string $html HTML source code
	 * @return string
	 */
	public static function removeDangerous(string $html): string
	{
		static $dangerous = [
			'~\\s+href="javascript[^"]*"~i',
			'~\\s+src="javascript[^"]*"~i',
			'~\\s+href="data:[^"]*"~i',	// See http://www.soom.cz/index.php?name=projects/testmail/main
			'~\\s+src="data:[^"]*"~i'
		];

		return preg_replace($dangerous, '', $html);
	}

	/**
	 * Returns <body> contents from the given HTML source.
	 * Expects valid HTML source.
	 *
	 * @param string $html HTML source code
	 * @return string
	 */
	public static function getBody(string $html): string
	{
		// If the source code contains <body>, return this element's contents
		if (preg_match('~<body([^>]*)>(.*?)</body>~is', $html, $matches)) {
			$body = trim($matches[2]);

			// Converts <body> inline styles to a newly created <div> element
			if (preg_match('~style="[^"]+"~i', $matches[1], $style)) {
				$body = '<div ' . $style[0] . '>' . $body . '</div>';
			}

			return $body;
		}

		// Return everything otherwise
		return $html;
	}

	/**
	 * Converts text to HTML source code.
	 *
	 * @param string $text Input text
	 * @param boolean $convertLinks Convert urls and emails to links
	 * @return string
	 */
	public static function fromText(string $text, bool $convertLinks = true): string
	{
		// Trimming whitespace (except spaces)
		$text = trim($text, "\r\n");

		// Two empty lines max
		$text = preg_replace("~\n\\s+\n~", "\n\n", $text);

		// Special chars
		$html = htmlspecialchars($text, ENT_QUOTES, 'utf-8', false);

		// Two spaces mean an indent, convert to non-breaking spaces
		$html = str_replace('  ', '&nbsp;&nbsp;', $html);
		// Convert tabs to four non-breaking spaces
		$html = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $html);

		// Paragraph
		$html = '<p>' . preg_replace("~\n\n[^\\n]?~", '</p><p>\\0', $html) . '</p>';
		$html = str_replace("\n", "<br />\n", $html);
		$html = str_ireplace('<p><br />', "<p>\n", $html);

		// Citation
		preg_match_all('~(?:(^(?:<p>)?\\s*&gt;(?:&gt;|\\s)*)(.*)$)|(?:.+)~im', $html, $matches);
		$html = '';
		$offset = 0;
		for ($i = 0; $i < count($matches[0]); $i++) {
			$currentOffset = substr_count($matches[1][$i], '&gt;');
			if ($currentOffset > 0) {
				if ($currentOffset > $offset) {
					$html .= str_repeat('<blockquote type="cite">', $currentOffset - $offset) . '<p>';
					$offset = $currentOffset;
				} elseif ($currentOffset < $offset) {
					$html .= '</p>' . str_repeat('</blockquote>', $offset - $currentOffset) . '<p>';
					$offset = $currentOffset;
				}

				$html .= $matches[2][$i];
			} else {
				if ($offset > 0) {
					$html .= '</p>' . str_repeat('</blockquote>', $offset) . '<p>';
					$offset = 0;
				}

				$html .= $matches[0][$i];
			}
		}
		if ($offset > 0) {
			$html .= '</p>' . str_repeat('</blockquote>', $offset);
		}

		// Removes empty lines that were created during previous processing
		$html = preg_replace('~(?:<br />)+</p></blockquote>~i', '</p></blockquote>', $html);
		$html = str_ireplace('<p><br /></p>', '', $html);
		$html = str_ireplace('<p><br />', '<p>', $html);

		// Emails and urls
		if ($convertLinks) {
			$html = self::linkFromText($html);
		}

		return $html;
	}

	/**
	 * Converts text to a link to an url or email.
	 *
	 * @param string $text Input text
	 * @return string
	 */
	public static function linkFromText(string $text): string
	{
		$patternGenericTld = '(?:tld|aero|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|asia|post|geo)';
		$patternTld = '(?-i:' . $patternGenericTld . '|[a-z]{2})';
		$patternDomain = '(?:(?:[a-z]|[a-z0-9](?:[\-a-z0-9]{0,61}[a-z0-9]))[.])*(?:[a-z0-9](?:[\-a-z0-9]{0,61}[a-z0-9])[.]' . $patternTld . ')';

		$pattern8bit = '(?:25[0-5]|2[0-4][0-9]|[0-1]?[0-9]?[0-9])';
		$patternIPv4 = '(?:' . $pattern8bit . '(?:[.]' . $pattern8bit . '){3})';

		// a:b:c:d:e:f:g:h
		$patternIpV6Variant8Hex = '(?:(?:[0-9a-f]{1,4}:){7}[0-9a-f]{1,4})';
		// Compressed a::b
		$patternIpV6VariantCompressedHex = '(?:(?:(?:[0-9a-f]{1,4}(?::[0-9a-f]{1,4})*)?)::(?:(?:[0-9a-f]{1,4}(?::[0-9a-f]{1,4})*)?))';
		// IPv4 mapped to  IPv6 a:b:c:d:e:f:w.x.y.z
		$patternIpV6VariantHex4Dec = '(?:(?:(?:[0-9a-f]{1,4}:){6})' . $patternIPv4 . ')';
		// Compressed IPv4 mapped to IPv6 a::b:w.x.y.z
		$patternIpV6VariantCompressedHex4Dec = '(?:(?:(?:[0-9a-f]{1,4}(?::[0-9a-f]{1,4})*)?)::(?:(?:[0-9a-f]{1,4}:)*)' . $patternIPv4 . ')';
		$patternIpV6 = '(?:' . $patternIpV6Variant8Hex . '|' . $patternIpV6VariantCompressedHex . '|' . $patternIpV6VariantHex4Dec . '|' . $patternIpV6VariantCompressedHex4Dec . ')';

		// mailto:username
		$patternEmail = '(?:mailto:)?(?:[\-\\w!#\$%&\'*+/=?^`{|}\~]+(?:[.][\-\\w!#\$%&\'*+/=?^`{|}\~]+)*)';
		// @domain.tld
		$patternEmail .= '(?:@' . $patternDomain . ')';

		// protocol://user:password@
		$patternUrl = '(?:(?:http|ftp)s?://(?:[\\S]+(?:[:][\\S]*)?@)?)?';
		// domain.tld, IPv4 or IPv6
		$patternUrl .= '(?:' . $patternDomain . '|' . $patternIPv4 . '|' . $patternIpV6 . ')';
		// :port/path/file.extension
		$patternUrl .= '(?::[0-9]+)?(?:(?:/[-\\w\\pL\\pN\~.:!%]+)*(?:/|[.][a-z0-9]{2,4})?)?';
		// ?query#hash
		$patternUrl .= '(?:[?][\]\[\-\\w\\pL\\pN.,?!\~%#@&;:/\'\=+]*)?(?:#[\]\[\-\\w\\pL\\pN.,?!\~%@&;:/\'\=+]*)?';

		return preg_replace_callback('~(^|[^\\pL\\pN])(?:(' . $patternEmail . ')|(' . $patternUrl . '))(?=$|\\W)~iu', function($matches) {
			// Url
			if (isset($matches[3])) {
				$url = $matches[3];
				// Remove special chars at the end
				if (preg_match('~(([.,:;?!>)\]}]|(&gt;))+)$~i', $url, $matches2)) {
					$punctuation = $matches2[1];
					// strlen is necessary because of &gt;
					$url = mb_substr($url, 0, -strlen($matches2[1]), 'utf-8');
				} else {
					$punctuation = '';
				}

				// Add missing http://
				$linkUrl = !preg_match('~^(http|ftp)s?://~i', $url) ? 'http://' .  $url : $url;

				// Create a link
				return $matches[1] . '<a href="' . $linkUrl . '">' . $url . '</a>' . $punctuation;
			}

			// Emails
			if (isset($matches[2])) {
				$email = $matches[2];
				if (false !== stripos($email, 'mailto:')) {
					$email = substr($matches[2], 7);
					$protocol = 'mailto:';
				} else {
					$protocol = '';
				}
				return $matches[1] . '<a href="mailto:' . $email . '">' . $protocol . $email . '</a>';
			}
		}, $text);
	}

	/**
	 * Converts HTML source code to plaintext.
	 *
	 * @param string $html HTML source code
	 * @return string
	 */
	public static function toText(string $html): string
	{
		$text = $html;

		// Remove styles a scripts
		$text = self::removeTags($text, ['style', 'script']);

		// Re-format lines
		// <pre>
		$text = preg_replace_callback('~<pre[^>]*>(.+?)</pre>~is', function($matches) {
			// Line breaks are converted to <br />, that are removed later
			return nl2br($matches[1]);
		}, $text);
		// \r, redundant line breaks, tabs and <br />
		$text = preg_replace(
			["~\r~", "~[\n\t]+~", '~<br[^>]*>~i'],
			['', ' ', "\n"],
			$text
		);

		// Processing of most tags and entities
		static $search = [
			'~<h[3-6][^>]*>(.+?)</h[3-6]>~is',	// <h3> to <h6>
			'~(<div[^>]*>)|(</div>)~i',			// <div> and </div>
			'~(<p(?:\s+[^>]+)?>)|(</p>)~i',		// <p> and </p>
			'~(<table[^>]*>)|(</table>)~i',		// <table> and </table>
			'~</tr>*~i',						// </tr>
			'~<td[^>]*>(.+?)</td>~is',			// <td> and </td>
			// '~(<code[^>]*>)|(</code>)~i', 	// <code> and </code>
			'~(&hellip;)~i',					// Ellipsis
			'~(&#8220;)|(&#8221;)~i',			// Quotes
			'~(&apos;)~i',						// Apostrophe
			'~(&copy;)|(&#169;)~i', 			// Copyright
			'~&trade;~i', 						// Trademark
			'~&reg;~i', 						// Registered trademark
			'~(&mdash;)|(&ndash;)~i' 			// Dash and hyphen
		];
		static $replace = [
			"\n\n\\1\n\n",	// <h3> to <h6>
			"\n\n",			// <div> and </div>
			"\n\n",			// <p> and </p>
			"\n\n",			// <table> and </table>
			"\n",			// </tr>
			"\\1\t",		// <td> and </td>
			// "\n\n",		// <code> and </code>
			'...',			// Ellipsis
			'"',			// Quotes
			'\'',			// Apostrophe
			'(c)',			// Copyright
			'(tm)',			// Trademark
			'(R)',			// Registered trademark
			'-'				// Dash and hyphen
		];
		$text = preg_replace($search, $replace, $text);

		// <h1> and <h2>
		$text = preg_replace_callback('~<h[12][^>]*>(.+?)</h[12]>~is', function($matches) {
			return "\n\n\n" . mb_strtoupper($matches[1], 'utf-8') . "\n\n";
		}, $text);
		// <strong>
		$text = preg_replace_callback('~<strong[^>]*>(.+?)</strong>~is', function($matches) {
			return mb_strtoupper($matches[1], 'utf-8');
		}, $text);
		// <hr />
		$text = preg_replace_callback('~<hr[^>]*>~i', function($matches) {
			return "\n" . str_repeat('-', 50) . "\n";
		}, $text);
		// <th>
		$text = preg_replace_callback('~<th[^>]*>(.+?)</th>~is', function($matches) {
			return mb_strtoupper($matches[1], 'utf-8') . "\t";
		}, $text);
		// <a>
		$text = self::linkToText($text);
		// <ul> and <ol>
		$text = self::listToText($text);

		// Two empty lines at most
		$text = trim($text, "\n ");
		$text = preg_replace("~\n\\s+\n~", "\n\n", $text);

		// Process <blockquote> (empty lines are removed before <blockquote> processing on purpose)
		$text = self::blockquoteToText($text);

		// Remove all left tags
		$text = strip_tags($text);

		// Replacing [textlink] for <> (must be done after strip_tags)
		$text = preg_replace('~\[textlink\]\\s*~s', '<', $text);
		$text = preg_replace('~\\s*\[/textlink\]~s', '>', $text);

		// Replaces non-breaking spaces
		$text = preg_replace(['~&nbsp;&nbsp;&nbsp;&nbsp;~i', '~&nbsp;~i'], ["\t", ' '], $text);

		// Remove other entities (must not be performed before)
		// After previous processing some entities are upper case, that is why we have to use strtolower
		$text = preg_replace_callback('~(&#?[a-z0-9]+;)~i', function($matches) {
			return html_entity_decode(strtolower($matches[1]), ENT_QUOTES, 'utf-8');
		}, $text);

		// Two empty lines at most (performed second times on purpose)
		$text = trim($text, "\n ");
		$text = preg_replace("~\n\\s+\n~", "\n\n", $text);
		// Because of <blockquote> converting
		$text = preg_replace("~(\n>\\s*)+\n~", "\n>\n", $text);

		// One space at most
		$text = preg_replace("~(\n|\t)( )+~", '\1', $text);
		$text = preg_replace('~( ){2,}~', ' ', $text);

		// No space at line ends
		$text = preg_replace("~[ \t]+\n~", "\n", $text);

		return $text;
	}

	/**
	 * Converts HTML links into plaintext.
	 *
	 * @param string $text Text with HTML fragments
	 * @return string
	 */
	private static function linkToText(string $text): string
	{
		return preg_replace_callback('~(<a\\s+[^>]*>)(.+?)</a>~is', function($matches) {
			$url = preg_match('~\\shref="([^"]+)"~i', $matches[1], $submatches) ? trim($submatches[1]) : '';
			$content = $matches[2];
			$clearContent = trim(strip_tags($content));

			// Some urls have no real meaning
			if ((empty($url)) || ('#' === $url[0]) || ('/?' === substr($url, 0, 2))) {
				return $content;
			}

			// Invalid url gets ignored
			if (!Input\Validator\IsUrl::validate($url)) {
				return $content;
			}

			// If the link text and target are the same, use only one of them
			if ($url === $clearContent) {
				return '[textlink]' . $content . '[/textlink]';
			} else {
				return $content . ' [textlink]' . $url . '[/textlink]';
			}
		}, $text);
	}

	/**
	 * Converts HTML lists to plaintext.
	 *
	 * @param string $text Text with HTML fragments
	 * @return string
	 */
	private static function listToText(string $text): string
	{
		static $symbols = ['#', '*', 'o', '+'];

		preg_match_all('~(?:<[a-z][a-z0-9]*[^>]*(?: /)?>)|(?:</[a-z][a-z0-9]*>)|(?:<![^>]+>)|(?:[^<]+)~i', $text, $matches);
		$text = '';
		$ulLevel = 0;
		$olLevel = 0;
		$olLiCount = [];
		$path = [];

		foreach ($matches[0] as $textPart) {
			if (0 === stripos($textPart, '<ol')) {
				array_push($path, 'ol');
				$olLevel++;
				$olLiCount[$olLevel] = 1;
				$textPart = "\n\n";
			} elseif ('</ol>' === strtolower($textPart)) {
				array_pop($path);
				$olLevel--;
				$textPart = "\n\n";
			} elseif (0 === stripos($textPart, '<ul')) {
				array_push($path, 'ul');
				$ulLevel++;
				$textPart = "\n\n";
			} elseif ('</ul>' === strtolower($textPart)) {
				array_pop($path);
				$ulLevel--;
				$textPart = "\n\n";
			} elseif (0 === stripos($textPart, '<li')) {
				$textPart = str_repeat("\t", $olLevel + $ulLevel);
				if ('ul' === end($path)) {
					$textPart .= $symbols[$ulLevel % 4] . ' ';
				} elseif ('ol' === end($path)) {
					$textPart .= $olLiCount[$olLevel] . '. ';
					$olLiCount[$olLevel]++;
				}
			} elseif ('</li>' === strtolower($textPart)) {
				$textPart = "\n";
			}

			$text .= $textPart;
		}

		return $text;
	}

	/**
	 * Converts citations into plaintext.
	 *
	 * @param string $text Text with HTML fragments
	 * @return string
	 */
	private static function blockquoteToText(string $text): string
	{
		if (preg_match_all('~(?:<blockquote[^>]*>\\s*)|(?:\\s*</blockquote>)|(?:.+?(?=</?blockquote)|(?:.+))~is', $text, $matches) > 0) {
			$text = '';
			$offset = 0;
			foreach ($matches[0] as $textPart) {
				if (($currentOffset = substr_count(strtolower($textPart), '<blockquote')) > 0) {
					$offset += $currentOffset;
					$textPart = ($offset == 1 ? "\n" : '');	// Adds a line to the beginning
				} elseif (($currentOffset = substr_count(strtolower($textPart), '</blockquote>')) > 0) {
					$offset -= $currentOffset;
					$textPart = '';
				} elseif ($offset > 0) {
					$textPart = "\n" . str_repeat('>', $offset) . ' '	// Opening tag
						. str_replace("\n", "\n" . str_repeat('>', $offset) . ' ', trim($textPart))	// Beginning of all lines
						. "\n" . str_repeat('>', $offset);	// Closing tag
				}

				$text .= $textPart;
			}
		}

		return $text;
	}
}
