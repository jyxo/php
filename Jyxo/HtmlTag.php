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

use InvalidArgumentException;
use function array_combine;
use function in_array;
use function is_array;
use function preg_match;
use function preg_match_all;
use function sprintf;
use function strtolower;
use function substr;
use const PREG_PATTERN_ORDER;

/**
 * Class for generating (x)HTML source code.
 * Allows creating HTML tags and its attributes.
 *
 * Example:
 * <code>
 * $p = \Jyxo\HtmlTag::create('p')->setClass('buttons');
 * </code>

 * The magic __call() method ensures attributes settings.
 *
 * <code>
 * $p->render();
 * </code>
 *
 * The render() method creates the HTML output.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Roman Řáha
 * @author Štěpán Svoboda
 */
final class HtmlTag
{

	/**
	 * Is XHTML output turned on?
	 *
	 * @var bool
	 */
	private $xhtml = true;

	/**
	 * Element name.
	 *
	 * @var string
	 */
	private $tag = '';

	/**
	 * Is the element self closing?
	 *
	 * @var bool
	 */
	private $isEmptyElement = false;

	/**
	 * Element attributes.
	 *
	 * @var array
	 */
	private $attributes = [];

	/**
	 * Array of child elements.
	 *
	 * @var array
	 */
	private $children = [];

	/**
	 * Array of elements whose value will not be escaped.
	 *
	 * @var array
	 */
	private $noEncode = [];

	/**
	 * Renders only the contents, not the opening and closing tag.
	 *
	 * @var bool
	 */
	private $contentOnly = false;

	/**
	 * List of self closing elements.
	 *
	 * @var array
	 */
	private $emptyElements = [
		'br' => true,
		'hr' => true,
		'img' => true,
		'input' => true,
		'meta' => true,
		'link' => true,
	];

	/**
	 * List of mandatory attributes that will be rendered even if empty.
	 *
	 * @var array
	 */
	private $requiredAttrs = [
		'option' => 'value',
		'optgroup' => 'label',
	];

	/**
	 * List of element attributes.
	 *
	 * @var array
	 */
	private static $attrs = [
		'accesskey' => true,
		'action' => true,
		'alt' => true,
		'cellpadding' => true,
		'cellspacing' => true,
		'checked' => true,
		'class' => true,
		'cols' => true,
		'disabled' => true,
		'for' => true,
		'href' => true,
		'id' => true,
		'label' => true,
		'method' => true,
		'name' => true,
		'onblur' => true,
		'onchange' => true,
		'onclick' => true,
		'onfocus' => true,
		'onkeyup' => true,
		'onsubmit' => true,
		'readonly' => true,
		'rel' => true,
		'rows' => true,
		'selected' => true,
		'size' => true,
		'src' => true,
		'style' => true,
		'tabindex' => true,
		'title' => true,
		'type' => true,
		'value' => true,
		'width' => true,
	];

	/**
	 * Constructor.
	 *
	 * Sets the element name.
	 *
	 * @param string $tag
	 */
	private function __construct(string $tag)
	{
		$this->tag = $tag;
	}

	/**
	 * Creates an element instance.
	 *
	 * @param string $tag HTML element name
	 * @return HtmlTag
	 */
	public static function create(string $tag): self
	{
		return new self($tag);
	}

	/**
	 * Returns an element instance from the given source.
	 * The first and last tag will be used as the opening and closing tag respectively.
	 * Anything between those tags will be used as contents.
	 *
	 * @param string $html HTML source code
	 * @return HtmlTag
	 */
	public static function createFromSource(string $html): self
	{
		if (preg_match('~<(\\w+)(\\s[^>]*)+>(.*)((<[^>]+>)?[^>]*)$~', $html, $matches)) {
			$tag = new self($matches[1]);

			if ($matches[3] !== '') {
				$tag->setText($matches[3]);
			}

			if (preg_match_all('/(\\w+)\\s*=\\s*"([^"]+)"/', $matches[2], $submatches, PREG_PATTERN_ORDER)) {
				$attrs = array_combine($submatches[1], $submatches[2]);
				$tag->setAttributes($attrs);
			}

			return $tag;
		}

		throw new InvalidArgumentException('Zadaný text neobsahuje validní html');
	}

	/**
	 * Creates and returns the opening tag.
	 *
	 * @return string
	 */
	public function open(): string
	{
		if ($this->contentOnly === true) {
			return '';
		}

		$this->isEmptyElement = isset($this->emptyElements[$this->tag]);
		$buff = '';

		foreach ($this->attributes as $name => $value) {
			if (isset(self::$attrs[$name])) {
				if (($name === 'selected' || $name === 'checked' || $name === 'readonly' || $name === 'disabled') && $value) {
					$value = $name;
				}

				$notEmpty = $value !== null && $value !== '' && $value !== false;

				if ($this->isRequiredAttr($this->tag, $name) || $notEmpty) {
					// For not empty attributes and the value attribute by the <option> tag
					if (!isset($this->noEncode[$name])) {
						$value = String::escape($value);
					}

					$attrString = sprintf(' %s="%s"', $name, $value);

					if ($name === 'value') {
						if ($this->tag === 'textarea') {
							$buff .= '';
						} else {
							$buff .= $attrString;
						}
					} else {
						$buff .= $attrString;
					}
				}
			}
		}

		$buff = '<' . $this->tag . $buff . ($this->xhtml ? $this->isEmptyElement ? ' />' : '>' : '>');

		return $buff;
	}

	/**
	 * Creates and returns element's contents.
	 *
	 * @return string
	 */
	public function content(): string
	{
		$buff = '';

		if (!$this->isEmptyElement) {

			$hasValue = isset($this->attributes['value']);
			$hasText = isset($this->attributes['text']);

			if ($hasValue || $hasText) {
				$text = $hasText ? $this->attributes['text'] : $this->attributes['value'];
				$noEncode = isset($this->noEncode['value']) || isset($this->noEncode['text']);
				// <script> contents are not escaped
				$noEncode = $this->tag === 'script' ? true : $noEncode;
				$buff .= $noEncode ? $text : String::escape($text);
			}
		}

		if (!$this->isEmptyElement && !empty($this->children)) {
			foreach ($this->children as $element) {
				$buff .= $element->render();
			}
		}

		return $buff;
	}

	/**
	 * Creates and returns the closing tag.
	 *
	 * @return string
	 */
	public function close(): string
	{
		if ($this->contentOnly === true) {
			return '';
		}

		$close = '</' . $this->tag . '>';

		if ($this->xhtml) {
			$buff = !$this->isEmptyElement ? $close : '';
		} else {
			$buff = $close;
		}

		$buff .= "\n";

		return $buff;
	}

	/**
	 * Renders the element.
	 *
	 * @return string
	 */
	public function render(): string
	{
		return $this->open() . $this->content() . $this->close();
	}

	/**
	 * Adds a child element.
	 *
	 * @param HtmlTag $element Child element to be added
	 * @return HtmlTag
	 */
	public function addChild(HtmlTag $element): self
	{
		$this->children[] = $element;

		return $this;
	}

	/**
	 * Adds multiple child elements.
	 *
	 * @param array $elements Array of child elements
	 * @return HtmlTag
	 */
	public function addChildren(array $elements): self
	{
		foreach ($elements as $element) {
			$this->addChild($element);
		}

		return $this;
	}

	/**
	 * Imports attributes from the given array.
	 *
	 * @param array $attributes Associative array of attributes and their values
	 * @return HtmlTag
	 */
	public function setAttributes(array $attributes): self
	{
		foreach ($attributes as $name => $value) {
			$this->attributes[strtolower($name)] = $value;
		}

		return $this;
	}

	/**
	 * Sets an attribute to not be espaced on output.
	 *
	 * @param string $attribute Attribute name
	 * @return HtmlTag
	 */
	public function setNoEncode(string $attribute): self
	{
		$this->noEncode[$attribute] = true;

		return $this;
	}

	/**
	 * Sets if only the contents should be rendered.
	 *
	 * @param bool $contentOnly Should only the contents be rendered
	 * @return HtmlTag
	 */
	public function setContentOnly(bool $contentOnly): selfs
	{
		$this->contentOnly = $contentOnly;

		return $this;
	}

	/**
	 * Returns if the given attribute is mandatory.
	 *
	 * @param string $tag HTML tag name
	 * @param string $attr Attribute name
	 * @return bool
	 */
	private function isRequiredAttr(string $tag, string $attr): bool
	{
		if (isset($this->requiredAttrs[$tag])) {
			if (is_array($this->requiredAttrs[$tag])) {
				return in_array($attr, $this->requiredAttrs[$tag], true);
			}

			return $attr === $this->requiredAttrs[$tag];
		}

		return false;
	}

	/**
	 * Renders the HTML element.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->render();
	}

	/**
	 * Sets or returns the attribute.
	 *
	 * @param string $method Method name
	 * @param array $args Method attributes
	 * @return mixed string|Jyxo_HtmlTag
	 */
	public function __call(string $method, array $args)
	{
		$type = $method[0] === 's' ? 'set' : 'get';

		if ($type === 'set') {
			$this->attributes[strtolower(substr($method, 3))] = $args[0];

			return $this;
		}

		if (isset($this->attributes[strtolower(substr($method, 3))])) {
			return $this->attributes[strtolower(substr($method, 3))];
		}

		return '';
	}

	/**
	 * Returns an attribute value.
	 *
	 * @param string $name Attribute name
	 * @return mixed string|null
	 */
	public function __get(string $name)
	{
		// An empty attribute is always null
		return $this->attributes[$name] ?? null;
	}

}
