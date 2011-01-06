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
 * Třída pro generování (x)HTML.
 * Umožňuje vytvářet html tagy podle $tags s atributy podle $attrs.
 *
 * např.
 * <code>
 * $p = \Jyxo\HtmlTag::create('p')->setClass('buttons');
 * </code>
 * Metoda __call() se stará o nastavování atributů.
 *
 * <code>
 * $p->render();
 * </code>
 * Metoda render() vytváří html výstup.
 *
 *
 * @category Jyxo
 * @package Jyxo
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Roman Řáha
 * @author Štěpán Svoboda
 */
final class HtmlTag
{
	/**
	 * Přepínač pro uzavírací závorku elementu.
	 *
	 * @var boolean
	 */
	private $xhtml = true;

	/**
	 * Název (x)html elementu.
	 *
	 * @var string
	 */
	private $tag = '';

	/**
	 * Určuje zda je element párový, nebo prázdný.
	 *
	 * @var boolean
	 */
	private $isEmptyElement = false;

	/**
	 * Atributy (x)html elementu.
	 *
	 * @var array
	 */
	private $attributes = array();

	/**
	 * Pole potomků elementu.
	 *
	 * @var array
	 */
	private $children = array();

	/**
	 * Pole atributů, které se nebudou při vykreslování kódovat.
	 *
	 * @var array
	 */
	private $noEncode = array();

	/**
	 * Renderuje se pouze obsah, bez obalení tagem.
	 *
	 * @var boolean
	 */
	private $contentOnly = FALSE;

	/**
	 * Seznam atributů elementů.
	 *
	 * @var array
	 */
	private static $attrs = array(
		'accesskey' => true, 'action' => true, 'alt' => true, 'cellpadding' => true, 'cellspacing' => true, 'checked' => true, 'class' => true,
		'cols' => true, 'disabled' => true, 'for' => true, 'href' => true, 'id' => true, 'label' => true, 'method' => true, 'name' => true, 'onblur' => true,
		'onchange' => true, 'onclick' => true, 'onfocus' => true, 'onkeyup' => true, 'onsubmit' => true, 'readonly' => true, 'rel' => true,
		'rows' => true, 'selected' => true, 'size' => true, 'src' => true, 'style' => true, 'tabindex' => true, 'title' => true, 'type' => true,
		'value' => true, 'width' => true,
	);

	/**
	 * Seznam nepárových elementů.
	 *
	 * @var array
	 */
	private $emptyElements = array(
		'br' => true, 'hr' => true, 'img' => true, 'input' => true, 'meta' => true, 'link' => true
	);

	/**
	 * Seznam povinných atributů, které se vykreslí vždy, i když jsou empty
	 *
	 * @var array
	 */
	private $requiredAttrs = array(
		'option' => 'value',
		'optgroup' => 'label'
	);

	/**
	 * Konstruktor nastavuje, o jaký tag se jedná.
	 *
	 * @param string $tag
	 */
	private function __construct($tag)
	{
		$this->tag = (string) $tag;
	}

	/**
	 * Vyrobí html element.
	 *
	 * @param string $tag
	 * @return \Jyxo\HtmlTag
	 */
	public static function create($tag)
	{
		return new self($tag);
	}

	/**
	 * Vrátí novou instanci html objektu, která je vytvořená parsováním předaného html.
	 * Z textu se vezme první tag a jako jeho ukončení se bere poslední zavírací tag.
	 * Obsah mezy tagy je nastaven jen jako text tagu.
	 *
	 * @param string $html
	 * @return \Jyxo\HtmlTag
	 * @throws \InvalidArgumentException Pokud nebylo zadáno validní html
	 */
	public static function createFromSource($html)
	{
		if (preg_match('~<(\w+)(\s[^>]*)+>(.*)((<[^>]+>)?[^>]*)$~', $html, $matches)) {
			$tag = new self($matches[1]);
			if ('' !== $matches[3]) {
				// @todo Možná dodělat rekurzi pro sestavení potomků.
				$tag->setText($matches[3]);
			}
			if (preg_match_all('/(\w+)\s*=\s*"([^"]+)"/', $matches[2], $submatches, PREG_PATTERN_ORDER)) {
				$attrs = array_combine($submatches[1], $submatches[2]);
				$tag->setAttributes($attrs);
			}
			return $tag;
		}
		throw new \InvalidArgumentException('Zadaný text neobsahuje validní html');
	}

	/**
	 * Vytvoří a vrátí otevírací tag.
	 *
	 * @return string
	 */
	public function open()
	{
		if (TRUE === $this->contentOnly) {
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
					// Pro neprázdné atributy a atribut value u tagu option
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
	 * Vytvoří a vrátí obsah html elementu.
	 *
	 * @return string
	 */
	public function content()
	{
		$buff = '';
		if (!$this->isEmptyElement) {

			$hasValue = isset($this->attributes['value']);
			$hasText = isset($this->attributes['text']);
			if ($hasValue || $hasText) {
				$text = $hasText ? $this->attributes['text'] : $this->attributes['value'];
				$noEncode = isset($this->noEncode['value']) || isset($this->noEncode['text']);
				// Text v tagu script se neescapuje.
				$noEncode = 'script' === $this->tag ? true : $noEncode;
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
	 * Vytvoří a vrátí uzavírací tag.
	 *
	 * @return string
	 */
	public function close()
	{
		if (true === $this->contentOnly) {
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
	 * Vyrenderuje element.
	 *
	 * @return string
	 */
	public function render()
	{
		return $this->open() . $this->content() . $this->close();
	}

	/**
	 * Přidá potomka.
	 *
	 * @param \Jyxo\HtmlTag $element
	 * @return \Jyxo\HtmlTag
	 */
	public function addChild(\Jyxo\HtmlTag $element)
	{
		$this->children[] = $element;
		return $this;
	}

	/**
	 *  Přidá potomky.
	 *
	 * @param array $elements
	 * @return \Jyxo\HtmlTag
	 */
	public function addChildren(array $elements)
	{
		foreach ($elements as $element) {
			$this->addChild($element);
		}
		return $this;
	}

	/**
	 * Importuje atributy z klíčovaného pole.
	 *
	 * @param array $attributes
	 * @return \Jyxo\HtmlTag
	 */
	public function setAttributes(array $attributes)
	{
		foreach ($attributes as $name => $value) {
			$this->attributes[strtolower($name)] = $value;
		}
		return $this;
	}

	/**
	 * Přidá atribut do pole nekódovaných atributů.
	 *
	 * @param string $attribute
	 * @return \Jyxo\HtmlTag
	 */
	public function setNoEncode($attribute)
	{
		$this->noEncode[$attribute] = true;

		return $this;
	}

	/**
	 * Nastaví, zda renderovat pouze obsah.
	 *
	 * @param boolean $contentOnly
	 * @return \Jyxo\HtmlTag
	 */
	public function setContentOnly($contentOnly)
	{
		$this->contentOnly = (bool) $contentOnly;
		return $this;
	}

	/**
	 * Renderuje html element.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Nastaví nebo vrátí atribut.
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed string|\Jyxo\HtmlTag
	 */
	public function __call($method, $args)
	{
		$type = $method[0] === 's' ? 'set' : 'get';
		if ($type === 'set') {
			$this->attributes[strtolower(substr($method, 3))] = $args[0];
			return $this;
		} else {
			if (isset($this->attributes[strtolower(substr($method, 3))])) {
				return $this->attributes[strtolower(substr($method, 3))];
			}
			return '';
		}
	}

	/**
	 * Vrací atribut.
	 *
	 * @param string $name
	 * @return mixed string|null
	 */
	public function __get($name)
	{
		// Prazdný atribut je vždy null
		return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
	}

	/**
	 * Vrací, zda je atribut povinný
	 *
	 * @param string $tag
	 * @param string $attr
	 */
	private function isRequiredAttr($tag, $attr)
	{
		if (isset($this->requiredAttrs[$tag])) {
			if (is_array($this->requiredAttrs[$tag])) {
				return in_array($attr, $this->requiredAttrs[$tag]);
			}
			return $attr == $this->requiredAttrs[$tag];
		}
		return false;
	}
}
