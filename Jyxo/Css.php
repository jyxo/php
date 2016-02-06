<?php

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
 * Class for working with CSS stylesheets.
 *
 * @category Jyxo
 * @package Jyxo\Css
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Css
{
	/**
	 * Constructor preventing from creating static class instances.
	 *
	 * @throws \LogicException When trying to create an instance
	 */
	public final function __construct()
	{
		throw new \LogicException(sprintf('Cannot create an instance of a static class %s.', get_class($this)));
	}

	/**
	 * Cleans up a CSS stylesheet.
	 *
	 * @param string $css Stylesheet definition
	 * @return string
	 */
	public static function repair($css)
	{
		// Convert properties to lowercase
		$css = preg_replace_callback('~((?:^|\{|;)\\s*)([\-a-z]+)(\\s*:)~i', function($matches) {
			return $matches[1] . strtolower($matches[2]) . $matches[3];
		}, $css);
		// Convert rgb() and url() to lowercase
		$css = preg_replace_callback('~(rgb|url)(?=\\s*\()~i', function($matches) {
			return strtolower($matches[1]);
		}, $css);
		// Remove properties without values
		$css = preg_replace_callback('~\\s*[\-a-z]+\\s*:\\s*([;}]|$)~i', function($matches) {
			return '}' === $matches[1] ? '}' : '';
		}, $css);
		// Remove MS Office properties
		$css = preg_replace('~\\s*mso-[\-a-z]+\\s*:[^;}]*;?~i', '', $css);
		// Convert color definitions to lowercase
		$css = preg_replace_callback('~(:[^:]*?)(#[abcdef0-9]{3,6})~i', function($matches) {
			return $matches[1] . strtolower($matches[2]);
		}, $css);
		// Convert colors from RGB to HEX
		$css = preg_replace_callback('~rgb\\s*\(\\s*(\\d+)\\s*,\\s*(\\d+)\\s*,\\s*(\\d+)\\s*\)~', function($matches) {
			return sprintf('#%02x%02x%02x', $matches[1], $matches[2], $matches[3]);
		}, $css);

		return $css;
	}

	/**
	 * Filters given properties.
	 *
	 * @param string $css Stylesheet definition
	 * @param array $properties Filtered properties
	 * @param boolean $exclude If true, $properties will be removed from the stylesheet; if false, only $properties will be left
	 * @return string
	 */
	public static function filterProperties($css, array $properties, $exclude = true)
	{
		$properties = array_flip($properties);
		return preg_replace_callback('~\\s*([\-a-z]+)\\s*:[^;}]*;?~i', function($matches) use ($properties, $exclude) {
			if ($exclude) {
				return isset($properties[$matches[1]]) ? '' : $matches[0];
			} else {
				return isset($properties[$matches[1]]) ? $matches[0] : '';
			}
		}, $css);
	}

	/**
	 * Removes unnecessary characters from a CSS stylesheet.
	 *
	 * It is recommended to use the repair() method on the stylesheet definition first.
	 *
	 * @param string $css Stylesheet definition
	 * @return string
	 */
	public static function minify($css)
	{
		// Comments
		$minified = preg_replace('~/\*.*\*/~sU', '', $css);
		// Whitespace
		$minified = preg_replace('~\\s*([>+\~,{:;}])\\s*~', '\\1', $minified);
		$minified = preg_replace('~\(\\s+~', '(', $minified);
		$minified = preg_replace('~\\s+\)~', ')', $minified);
		$minified = trim($minified);
		// Convert colors from #ffffff to #fff
		$minified = preg_replace('~(:[^:]*?#)([abcdef0-9]{1})\\2([abcdef0-9]{1})\\3([abcdef0-9]{1})\\4~', '\\1\\2\\3\\4', $minified);
		// Empty selectors
		$minified = preg_replace('~(?<=})[^{]+\{\}~', '', $minified);
		// Remove units when 0
		$minified = preg_replace('~([\\s:]0)(?:px|pt|pc|in|mm|cm|em|ex|%)~', '\\1', $minified);
		// Unnecessary semicolons
		$minified = str_replace(';}', '}', $minified);
		$minified = trim($minified, ';');

		return $minified;
	}

	/**
	 * Removes unnecessary characters from a CSS stylesheet.
	 *
	 * Use minify() instead.
	 *
	 * @deprecated
	 */
	public static function pack($css)
	{
		return self::minify($css);
	}

	/**
	 * Converts HTML styles inside <style> elements to inline styles.
	 *
	 * Supported selectors:
	 * * a {...}
	 * * #header {...}
	 * * .icon {...}
	 * * h1#header {...}
	 * * a.icon.small {...}
	 * * a#remove.icon.small {...}
	 * * a img {...}
	 * * h1, h2 {...}
	 * * a:link {...} - converts to a {...}
	 *
	 * @param string $html Processed HTML source
	 * @return string
	 */
	public static function convertStyleToInline($html)
	{
		// Extract styles from the source
		$cssList = self::parseStyle($html);

		// If no styles were found, return the original HTML source
		if (empty($cssList)) {
			return $html;
		}

		// Parse the HTML source
		preg_match_all('~(?:<\\w+[^>]*(?: /)?>)|(?:</\\w+>)|(?:<![^>]+>)|(?:[^<]+)~', $html, $matches);
		$path = [];
		$html = '';
		$inStyle = false;
		foreach ($matches[0] as $htmlPart) {
			// Skip <style> elements
			if (0 === strpos($htmlPart, '<style')) {
				// <style> opening tag
				$inStyle = true;
			} elseif (0 === strpos($htmlPart, '</style')) {
				// <style> closing tag
				$inStyle = false;
			} elseif (!$inStyle) {
				// Not inside the <style> element

				// Closing tag
				if (0 === strpos($htmlPart, '</')) {
					array_pop($path);
				} elseif (('<' === $htmlPart[0]) && (0 !== strpos($htmlPart, '<!'))) {
					// Opening tag or empty element, ignoring comments

					$htmlPart = trim($htmlPart, '/<> ');

					$attributeList = ['id' => '', 'class' => ''];
					// If there is no space, there are no attributes
					if (false !== strpos($htmlPart, ' ')) {
						list($tag, $attributes) = explode(' ', $htmlPart, 2);

						// Parse attributes
						foreach (explode('" ', $attributes) as $attribute) {
							list($attributeName, $attributeContent) = preg_split('~=["\']~', $attribute);
							$attributeList[$attributeName] = trim($attributeContent, '"\'');
						}
					} else {
						$tag = $htmlPart;
					}
					$attributeClass = !empty($attributeList['class']) ? explode(' ', $attributeList['class']) : [];

					// Add element information to the path
					array_push($path, [
							'tag' => $tag,
							'id' => $attributeList['id'],
							'class' => $attributeClass
						]
					);

					// Walk through the CSS definition list and add applicable properties
					// Because of inheritance, walk the array in reversed order
					foreach (array_reverse($cssList) as $css) {
						// Selectors have to have equal or less parts than the HTML element nesting level
						if (count($css['selector']) > count($path)) {
							continue;
						}

						// The last selector part must correspond to the last processed tag
						$lastSelector = end($css['selector']);
						if (((!empty($lastSelector['tag'])) && ($tag !== $lastSelector['tag']))
								|| ((!empty($lastSelector['id'])) && ($attributeList['id'] !== $lastSelector['id']))
								|| (count(array_diff($lastSelector['class'], $attributeClass)) > 0)) {
							continue;
						}

						$add = true;
						$lastPathKey = count($path);
						foreach (array_reverse($css['selector']) as $selector) {
							// Nothing was found in the previous search, no reason to continue searching
							if (!$add) {
								break;
							}

							for ($i = ($lastPathKey - 1); $i >= 0; $i--) {
								if (((empty($selector['tag'])) || ($path[$i]['tag'] === $selector['tag']))
										&& ((empty($selector['id'])) || ($path[$i]['id'] === $selector['id']))
										&& (0 === count(array_diff($selector['class'], $path[$i]['class'])))) {
									$lastPathKey = $i;
									continue 2;
								}
							}

							$add = false;
						}

						if ($add) {
							// Add a semicolon if missing
							if (';' !== substr($css['rules'], -1)) {
								$css['rules'] .= ';';
							}
							// CSS is processed in the reversed order, co place to the beginning
							$attributeList['style'] = $css['rules'] . (isset($attributeList['style']) ? $attributeList['style'] : '');
						}
					}

					// Creates a new tag
					$htmlPart = '<' . $tag;
					foreach ($attributeList as $attributeName => $attributeContent) {
						// Not using empty() because it would make attributes with the "0" value ignored
						if ('' !== $attributeContent) {
							$htmlPart .= ' ' . $attributeName . '="' . $attributeContent . '"';
						}
					}

					// Empty tags
					switch ($tag) {
						case 'br':
						case 'hr':
						case 'img':
						case 'input':
							$htmlPart .= ' />';
							break;
						default:
							$htmlPart .= '>';
							break;
					}
				}
			}

			// Append the part to the HTML source
			$html .= $htmlPart;
		}

		// In case of float: add a cleaner (if there is none present already)
		$cleaner = '<div style="clear: both; visibility: hidden; overflow: hidden; height: 1px;">';
		if ((preg_match('~<\\w+[^>]+style="[^"]*float:[^"]*"~', $html))
				&& (!preg_match('~' . preg_quote($cleaner) . '\\s*</body>\\s*</html>\\s*$~', $html))) {
			$html = str_replace('</body>', $cleaner . '</body>', $html);
		}

		return $html;
	}

	/**
	 * Helper method for searching and parsing <style> definitions inside a HTML source.
	 *
	 * @param string $html Processed HTML source
	 * @return array
	 * @see \Jyxo\Css::convertStyleToInline()
	 */
	private static function parseStyle($html)
	{
		// Find <style> elements
		if (!preg_match_all('~<style\\s+(?:[^>]+\\s+)*type="text/css"[^>]*>(.*?)</style>~s', $html, $styles)) {
			return [];
		}

		$cssList = [];
		foreach ($styles[1] as $style) {
			// Remove CDATA and HTML comments
			$style = str_replace(['<![CDATA[', ']]>', '<!--', '-->'], '', $style);

			// Optimize the parsed definitions
			$style = self::minify($style);

			if (empty($style)) {
				continue;
			}

			// Replace quotes with apostrophes
			$style = str_replace('"', "'", $style);

			// Remove the last empty part
			$definitions = explode('}', $style, -1);

			foreach ($definitions as $definition) {
				// Allows only supported selectors with valid rules
				if (!preg_match('~^(?:(?:(?:[\-_\\w#.:]+)\\s?)+,?)+{(?:[-\\w]+:[^;]+[;]?)+$~', $definition)) {
					continue;
				}

				list($selector, $rules) = explode('{', $definition);
				foreach (explode(',', $selector) as $part) {
					// Convert a:link to a
					$part = str_replace(':link', '', $part);

					$parsedSelector = [];
					foreach (explode(' ', $part) as $selectorPart) {
						// If no tag name was given use a fake one
						if (('.' === $selectorPart[0]) || ('#' === $selectorPart[0])) {
							$selectorPart = ' ' . $selectorPart;
						}

						if (false !== strpos($selectorPart, '.')) {
							list($selectorPart, $class) = explode('.', $selectorPart, 2);
							// There can be multiple classes
							$class = explode('.', $class);
						} else {
							$class = [];
						}
						if (false !== strpos($selectorPart, '#')) {
							list($selectorPart, $id) = explode('#', $selectorPart, 2);
						} else {
							$id = '';
						}
						$tag = trim($selectorPart);

						$parsedSelector[] = [
							'tag' => strtolower($tag),
							'id' => $id,
							'class' => $class
						];
					}

					$cssList[] = [
						'selector' => $parsedSelector,
						'rules' => $rules
					];
				}
			}
		}

		return $cssList;
	}
}
