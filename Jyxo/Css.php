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
	public static function repair(string $css): string
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
	public static function filterProperties(string $css, array $properties, bool $exclude = true): string
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
	public static function minify(string $css): string
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
	 * * a > img {...}
	 * * li + li {...}
	 * * a#remove.icon.small img {...}
	 * * h1, h2 {...}
	 * * p a:first-child {...}
	 * * p a:last-child {...}
	 * * p a:nth-child(...) {...}
	 * * p a:nth-last-child(...) {...}
	 * * p a:first-of-type {...}
	 * * p a:last-of-type {...}
	 * * p a:nth-of-type(...) {...}
	 * * p a:nth-last-of-type(...) {...}
	 * * a:link {...} - converts to a {...}
	 *
	 * @param string $html Processed HTML source
	 * @return string
	 */
	public static function convertStyleToInline(string $html): string
	{
		// Extract styles from the source
		$cssList = self::parseStyle($html);

		// If no styles were found, return the original HTML source
		if (empty($cssList)) {
			return $html;
		}

		// Parse the HTML source
		preg_match_all('~(?:<\\w+[^>]*(?:\\s*/)?>)|(?:</\\w+>)|(?:<!--)|(?:<!\[endif\]-->)|(?:<!\[CDATA\[.+?\]\]>)|(?:<!DOCTYPE[^>]+>)|(?:[^<]+)~s', $html, $matches);

		$level = 0;
		$path = [];
		$nodeNo = 0;
		$nodes = [];
		foreach ($matches[0] as $match) {
			if (0 === strpos($match, '</')) {
				$level--;
				array_pop($path[$level]);
				$nodes[$nodeNo] = [
					'number' => $nodeNo,
					'type' => 'closing-tag',
					'content' => $match,
					'level' => $level
				];
			} elseif ('<' === $match[0] && 0 !== strpos($match, '<!')) {
				list($tag, $attributes) = preg_split('~(?:\\s+|/|$)~', trim($match, '<>'), 2);
				$tag = strtolower($tag);
				$id = null;
				$class = [];

				if (preg_match('~(?:^|\\s)id=(?:(?:(["\'])([^\\1]+?)\\1)|(\\S+))~', $attributes, $matches)) {
					$id = $matches[3] ?? $matches[2];
				}

				if (preg_match('~(?:^|\\s)class=(?:(?:(["\'])([^\\1]+?)\\1)|(\\S+))~', $attributes, $matches)) {
					$class = preg_split('~\\s+~', $matches[3] ?? $matches[2]);
				}

				$path[$level][] = $nodeNo;

				$parent = null;
				if ($level > 0) {
					$parent = end($path[$level - 1]);
					$nodes[$parent]['children'][] = $nodeNo;
				}

				$nodes[$nodeNo] = [
					'number' => $nodeNo,
					'type' => 'opening-tag',
					'content' => $match,
					'level' => $level,
					'parent' => $parent,
					'children' => [],
					'tag' => $tag,
					'id' => $id,
					'class' => $class,
				];

				static $emptyTags = ['br', 'hr', 'img', 'input', 'link', 'meta', 'source', 'track', 'param', 'area', 'command', 'col', 'base', 'keygen', 'wbr'];
				if (!in_array($tag, $emptyTags, true)) {
					$level++;
				}

			} else {
				$nodes[$nodeNo] = [
					'number' => $nodeNo,
					'type' => 'other',
					'content' => $match,
					'level' => $level
				];
			}

			$nodeNo++;
		}

		$checkIfNodeMatchesSelector = function (array $node, array $selector) use ($nodes): bool {
			if ((null !== $selector['tag'] && $node['tag'] !== $selector['tag'])
				|| (null !== $selector['id'] && $node['id'] !== $selector['id'])
				|| count(array_diff($selector['class'], $node['class'])) > 0) {
				return false;
			}

			if (0 === count($selector['pseudoClass'])) {
				return true;
			} elseif (null === $node['parent']) {
				return false;
			}

			$siblings = $nodes[$node['parent']]['children'];
			$positionAmongSiblings = array_search($node['number'], $siblings, true);
			if (false === $positionAmongSiblings) {
				return false;
			}

			$sameTypeSiblings = array_values(array_filter($siblings, function (int $siblingNo) use ($nodes, $node): bool {
				return $node['tag'] === $nodes[$siblingNo]['tag'];
			}));
			$positionAmongSameTypeSiblings = array_search($node['number'], $sameTypeSiblings, true);

			// CSS is counting from one
			$positionAmongSiblings++;
			if ($positionAmongSameTypeSiblings !== false) {
				$positionAmongSameTypeSiblings++;
			}

			foreach ($selector['pseudoClass'] as $pseudoClass) {
				$match = false;
				if ('first-child' === $pseudoClass) {
					$match = 1 === $positionAmongSiblings;
				} elseif ('first-of-type' === $pseudoClass) {
					$match = 1 === $positionAmongSameTypeSiblings;
				} elseif ('last-child' === $pseudoClass) {
					$match = count($siblings) === $positionAmongSiblings;
				} elseif ('last-of-type' === $pseudoClass) {
					$match = count($sameTypeSiblings) === $positionAmongSameTypeSiblings;
				} elseif (preg_match('~^nth-(child|of-type)\(([^\)]+)\)$~', $pseudoClass, $matches)) {
					if ('child' === $matches[1]) {
						$position = $positionAmongSiblings;
					} else {
						if (false === $positionAmongSameTypeSiblings) {
							return false;
						}
						$position = $positionAmongSameTypeSiblings;
					}
					$figure = $matches[2];

					if ('odd' === $figure) {
						$match = 1 === $position % 2;
					} elseif ('even' === $figure) {
						$match = 0 === $position % 2;
					} elseif (preg_match('~^(\\d+)n(?:\+(\\d+))?$~', $figure, $figureMatches)
							|| preg_match('~^\+?(\\d+)$~', $figure, $figureMatches)
							|| preg_match('~^-(\\d+)n\+(\\d+)$~', $figure, $figureMatches)) {
						$a = (int) $figureMatches[1];
						$b = (int) ($figureMatches[2] ?? 0);
						$difference = $b - $position;
						$match = 0 === ($a === 0 ? $difference : $difference % $a);
					}
				} elseif (preg_match('~^nth-last-(child|of-type)\(([^\)]+)\)$~', $pseudoClass, $matches)) {
					if ('child' === $matches[1]) {
						$position = $positionAmongSiblings;
						$siblingsCount = count($siblings);
					} else {
						if (false === $positionAmongSameTypeSiblings) {
							return false;
						}

						$position = $positionAmongSameTypeSiblings;
						$siblingsCount = count($sameTypeSiblings);
					}
					$figure = $matches[2];

					if ('even' === $figure) {
						$match = (0 === $siblingsCount % 2 ? 1 : 0) === $position % 2;
					} elseif ('odd' === $figure) {
						$match = (0 === $siblingsCount % 2 ? 0 : 1) === $position % 2;
					} elseif (preg_match('~^(\\d+)n(?:\+(\\d+))?$~', $figure, $figureMatches)
							|| preg_match('~^\+?(\\d+)$~', $figure, $figureMatches)
							|| preg_match('~^-(\\d+)n\+(\\d+)$~', $figure, $figureMatches)) {
						$a = (int) $figureMatches[1];
						$b = (int) ($figureMatches[2] ?? 0);
						$difference = $siblingsCount + 1 - $position - $b;
						$match = 0 === ($a === 0 ? $difference : $difference % $a);
					}
				}

				if (!$match) {
					return false;
				}
			}

			return true;
		};

		$html = '';
		foreach ($nodes as $nodeNo => $node) {
			if ('opening-tag' === $node['type']) {
				$inlineStyles = [];
				$styles = [];

				$addStyle = function (string $rule, array $selectors = null) use (&$styles) {
					list($property, $propertyValue) = explode(':', $rule, 2);

					$styles[$property][] = [
						'value' => $propertyValue,
						'selectors' => $selectors
					];
				};

				if (preg_match('~\\s+style=((?:(["\'])([^\\2]+?)\\2)|(\\S+))~', $node['content'], $matches)) {
					$styleContent = $matches[4] ?? $matches[3];
					if ("'" === $matches[2]) {
						$styleContent = strtr($styleContent, ['"' => "'", "\\'" => "'"]);
					}

					$inlineStyles = explode(';', self::minify($styleContent));
				}

				// Walk through the CSS definition list and add applicable properties
				foreach ($cssList as $css) {
					$selectorPartsCount = count($css['selector']);

					// Selectors have to have equal or less parts than the HTML element nesting level
					if ($selectorPartsCount > ($node['level'] + 1)) {
						continue;
					}

					// The last selector part must correspond to the last processed tag
					$lastSelector = end($css['selector']);
					if (!$checkIfNodeMatchesSelector($node, $lastSelector)) {
						continue;
					}

					$selectorMatched = true;

					if ($selectorPartsCount > 1) {
						$previousSelector = $lastSelector;
						$currentNode = $node;

						// Skip last selector, it was already checked
						for ($i = $selectorPartsCount - 2; $i >= 0; $i--) {
							$selector = $css['selector'][$i];

							if ($previousSelector['type'] === 'sibling') {
								$siblings = $nodes[$currentNode['parent']]['children'];
								$positionAmongSiblings = array_search($currentNode['number'], $siblings, true);

								if ($positionAmongSiblings !== 0 && $checkIfNodeMatchesSelector($nodes[$siblings[$positionAmongSiblings - 1]], $selector)) {
									$currentNode = $nodes[$siblings[$positionAmongSiblings - 1]];
									$previousSelector = $selector;

									continue;
								}
							} else {
								$startSearchLevel = $currentNode['level'] - 1;

								if ($previousSelector['type'] === 'child') {
									$endSearchLevel = $startSearchLevel;
								} else {
									$endSearchLevel = 0;
								}

								for ($j = $startSearchLevel; $j >= $endSearchLevel; $j--) {
									$currentNode = $nodes[$currentNode['parent']];

									if ($checkIfNodeMatchesSelector($currentNode, $selector)) {
										$previousSelector = $selector;
										continue 2;
									}
								}
							}

							$selectorMatched = false;
							break;
						}
					}

					if ($selectorMatched) {
						foreach (explode(';', $css['rules']) as $rule) {
							$addStyle($rule, $css['selector']);
						}
					}
				}

				// Adds inline styles to the end
				foreach ($inlineStyles as $rule) {
					$addStyle($rule);
				}

				// Adds styles to HTML part
				if (count($styles) > 0) {
					$styleContent = '';

					foreach ($styles as $property => $propertyData) {
						uksort($propertyData, function (int $a, int $b) use ($propertyData): int {
							$aHasImportant = false !== strpos($propertyData[$a]['value'], '!important');
							$bHasImportant = false !== strpos($propertyData[$b]['value'], '!important');

							if ($aHasImportant && !$bHasImportant) {
								return 1;
							} elseif (!$aHasImportant && $bHasImportant) {
								return -1;
							}

							$aIsInline = null === $propertyData[$a]['selectors'];
							$bIsInline = null === $propertyData[$b]['selectors'];

							if ($aIsInline && !$bIsInline) {
								return 1;
							} elseif (!$aIsInline && $bIsInline) {
								return -1;
							}

							$priority = function (array $selectors): int {
								$priority = 0;
								foreach ($selectors as $selector) {
									if (null !== $selector['id']) {
										$priority += 10000;
									}
									$classCount = count($selector['class']) + count($selector['pseudoClass']);
									if ($classCount > 0) {
										$priority += 100 * $classCount;
									}
									if (null !== $selector['tag']) {
										$priority += 1;
									}
								}

								return $priority;
							};

							$aPriority = $priority($propertyData[$a]['selectors']);
							$bPriority = $priority($propertyData[$b]['selectors']);

							if ($aPriority !== $bPriority) {
								return $aPriority <=> $bPriority;
							} else {
								return $a <=> $b;
							}
						});

						$styleContent .= sprintf('%s:%s;', $property, rtrim(end($propertyData)['value'], ';'));
					}

					$styleAttribute = sprintf('style="%s"', rtrim($styleContent, ';'));
					$node['content'] = preg_replace_callback('~(?:(\\s+)style=(?:(?:(["\'])(?:[^\\2]+?)\\2)|(?:\\S+)))|(\\s*/?>$)~', function (array $matches) use ($styleAttribute) {
						$before = $matches[1];
						if (isset($matches[3])) {
							$before = ' ';
						}

						$after = $matches[3] ?? '';

						return $before . $styleAttribute . $after;
					}, $node['content'], 1);
				}
			}

			// Append the part to the HTML source
			$html .= $node['content'];
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
	private static function parseStyle(string $html): array
	{
		// Remove conditional comments
		$html = preg_replace('~<!--\[if[^\]]*\]>.*?<!\[endif\]-->~s', '', $html);

		// Find <style> elements
		if (!preg_match_all('~<style\\s+(?:[^>]+\\s+)*type="text/css"[^>]*>(.*?)</style>~s', $html, $styles)) {
			return [];
		}

		$cssList = [];
		foreach ($styles[1] as $style) {
			// Remove CDATA and comments
			$style = str_replace(['<![CDATA[', ']]>', '<!--', '-->'], '', $style);
			$style = preg_replace('~/\*.*\*/~sU', '', $style);

			// Optimize the parsed definitions
			$style = self::minify($style);

			if (empty($style)) {
				continue;
			}

			// Replace double quotes with single quotes
			$style = strtr($style, ['"' => "'", "\\'" => "'"]);

			// Remove the last empty part
			$definitions = explode('}', $style, -1);

			foreach ($definitions as $definition) {
				// Allows only supported selectors with valid rules
				if (!preg_match('~^(?:(?:(?:(?:[#.]?[-\\w]+)+(?::[-\\w\(\)+]+)?)[\\s>+]*)+,?)+{(?:[-\\w]+:[^;]+[;]?)+$~', $definition)) {
					continue;
				}

				list($selector, $rules) = explode('{', $definition);
				foreach (explode(',', $selector) as $part) {
					// Convert a:link to a
					$part = str_replace(':link', '', $part);

					$parsedSelector = [];
					$type = null;

					if (!preg_match_all('~((?:[#.]?[-\\w]+)+(?::[-\\w\(\)+]+)?)|([+>\\s])~', $part, $matches, PREG_SET_ORDER)) {
						continue;
					}

					foreach ($matches as $match) {
						if (isset($match[2])) {
							switch ($match[2]) {
								case '+':
									$type = 'sibling';
									break;
								case '>':
									$type = 'child';
									break;
								default:
									$type = 'descendant';
									break;
							}
							continue;
						}

						$selectorPart = $match[1];

						if (false !== strpos($selectorPart, ':')) {
							list($selectorPart, $pseudoClass) = explode(':', $selectorPart, 2);
							// There can be multiple pseudo-classes
							$pseudoClass = explode(':', $pseudoClass);
						} else {
							$pseudoClass = [];
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
							$id = null;
						}
						$tag = strtolower(trim($selectorPart));
						if ($tag === '') {
							$tag = null;
						}

						$parsedSelector[] = [
							'type' => $type,
							'tag' => $tag,
							'id' => $id,
							'class' => $class,
							'pseudoClass' => $pseudoClass,
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
