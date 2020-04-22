<?php
/**
 * 2008 - 2015 HDClic
 *
 * MODULE PrestaBlogcreateChildR
 *
 * @version   3.6.3
 * @author    HDClic <prestashop@hdclic.com>
 * @link      http://www.hdclic.com
 * @copyright Copyright (c) permanent, HDClic
 * @license   Addons PrestaShop license limitation
 *
 * NOTICE OF LICENSE
 *
 * Don't use this module on several shops. The license provided by PrestaShop Addons
 * for all its modules is valid only once for a single shop.
 */

class Xml
{

/**
 * Initialize SimpleXMLElement or DOMDocument from a given XML string, file path, URL or array.
 *
 * ### Usage:
 *
 * Building XML from a string:
 *
 * `$xml = Xml::build('<example>text</example>');`
 *
 * Building XML from string (output DOMDocument):
 *
 * `$xml = Xml::build('<example>text</example>', array('return' => 'domdocument'));`
 *
 * Building XML from a file path:
 *
 * `$xml = Xml::build('/path/to/an/xml/file.xml');`
 *
 * Building from a remote URL:
 *
 * `$xml = Xml::build('http://example.com/example.xml');`
 *
 * Building from an array:
 *
 * {{{
 * 	$value = array(
 * 		'tags' => array(
 * 			'tag' => array(
 * 				array(
 * 					'id' => '1',
 * 					'name' => 'defect'
 * 				),
 * 				array(
 * 					'id' => '2',
 * 					'name' => 'enhancement'
 *				)
 * 			)
 * 		)
 * 	);
 * $xml = Xml::build($value);
 * }}}
 *
 * When building XML from an array ensure that there is only one top level element.
 *
 * ### Options
 *
 * - `return` Can be 'simplexml' to return object of SimpleXMLElement or 'domdocument' to return DOMDocument.
 * - `loadEntities` Defaults to false. Set to true to enable loading of `<!ENTITY` definitions. This
 *   is disabled by default for security reasons.
 * - If using array as input, you can pass `options` from Xml::fromArray.
 *
 * @param string|array $input XML string, a path to a file, an URL or an array
 * @param array $options The options to use
 * @return SimpleXMLElement|DOMDocument SimpleXMLElement or DOMDocument
 * @throws XmlException
 */
	public static function build($input, $options = array())
	{
		if (!is_array($options))
			$options = array('return' => (string)$options);

		$defaults = array(
			'return' => 'simplexml',
			'loadEntities' => false,
		);
		$options = array_merge($defaults, $options);

		if (is_array($input) || is_object($input))
			return self::fromArray((array)$input, $options);
		elseif (strpos($input, '<') !== false)
			return self::loadXmlR($input, $options);
		elseif (file_exists($input))
			return self::loadXmlR(Tools::file_get_contents($input), $options);
	}

/**
 * Parse the input data and create either a SimpleXmlElement object or a DOMDocument.
 *
 * @param string $input The input to load.
 * @param array $options The options to use. See Xml::build()
 * @return SimpleXmlElement|DOMDocument
 * @throws XmlException
 */
	protected static function loadXmlR($input, $options)
	{
		$has_disable = function_exists('libxml_disable_entity_loader');
		$internal_errors = libxml_use_internal_errors(true);
		if ($has_disable && !$options['loadEntities'])
			libxml_disable_entity_loader(true);
		try
		{
			if ($options['return'] === 'simplexml' || $options['return'] === 'simplexmlelement')
				$xml = new SimpleXMLElement($input, LIBXML_NOCDATA);
			else
			{
				$xml = new DOMDocument();
				$xml->loadXML($input);
			}
		}
		catch (Exception $e)
		{
			$xml = null;
		}

		if ($has_disable && !$options['loadEntities'])
			libxml_disable_entity_loader(false);

		libxml_use_internal_errors($internal_errors);
		if ($xml === null)
			throw new Exception('Xml cannot be read.');

		return $xml;
	}

/**
 * Transform an array into a SimpleXMLElement
 *
 * ### Options
 *
 * - `format` If create childs ('tags') or attributes ('attribute').
 * - `version` Version of XML document. Default is 1.0.
 * - `encoding` Encoding of XML document. If null remove from XML header. Default is the some of application.
 * - `return` If return object of SimpleXMLElement ('simplexml') or DOMDocument ('domdocument'). Default is SimpleXMLElement.
 *
 * Using the following data:
 *
 * {{{
 * $value = array(
 *    'root' => array(
 *        'tag' => array(
 *            'id' => 1,
 *            'value' => 'defect',
 *            '@' => 'description'
 *         )
 *     )
 * );
 * }}}
 *
 * Calling `Xml::fromArray($value, 'tags');`  Will generate:
 *
 * `<root><tag><id>1</id><value>defect</value>description</tag></root>`
 *
 * And calling `Xml::fromArray($value, 'attribute');` Will generate:
 *
 * `<root><tag id="1" value="defect">description</tag></root>`
 *
 * @param array $input Array with data
 * @param array $options The options to use
 * @return SimpleXMLElement|DOMDocument SimpleXMLElement or DOMDocument
 * @throws XmlException
 */
	public static function fromArray($input, $options = array())
	{
		if (!is_array($input) || count($input) !== 1)
			throw new Exception('Invalid input.');

		$key = key($input);
		if (is_int($key))
			throw new Exception('The key of input must be alphanumeric');

		if (!is_array($options))
			$options = array('format' => (string)$options);

		$defaults = array(
			'format' => 'tags',
			'version' => '1.0',
			'encoding' => Configure::read('App.encoding'),
			'return' => 'simplexml'
		);
		$options = array_merge($defaults, $options);

		$dom = new DOMDocument($options['version'], $options['encoding']);
		self::fromArrayR($dom, $dom, $input, $options['format']);

		$options['return'] = Tools::strtolower($options['return']);
		if ($options['return'] === 'simplexml' || $options['return'] === 'simplexmlelement')
			return new SimpleXMLElement($dom->saveXML());

		return $dom;
	}

/**
 * Recursive method to create childs from array
 *
 * @param DOMDocument $dom Handler to DOMDocument
 * @param DOMElement $node Handler to DOMElement (child)
 * @param array $data Array of data to append to the $node.
 * @param string $format Either 'attribute' or 'tags'. This determines where nested keys go.
 * @return void
 * @throws XmlException
 */
	protected static function fromArrayR($dom, $node, &$data, $format)
	{
		if (empty($data) || !is_array($data))
			return;

		foreach ($data as $key => $value)
		{
			if (is_string($key))
			{
				if (!is_array($value))
				{
					if (is_bool($value))
						$value = (int)$value;
					elseif ($value === null)
						$value = '';

					$is_namespace = strpos($key, 'xmlns:');
					if ($is_namespace !== false)
					{
						$aa = 'http';
						$bb = 'www.w3.org/2000/xmlns/';
						$node->setAttributeNS($aa.'://'.$bb, $key, $value);
						continue;
					}
					if ($key[0] !== '@' && $format === 'tags')
					{
						$child = null;
						if (!is_numeric($value))
						{
							// Escape special characters
							// http://www.w3.org/TR/REC-xml/#syntax
							// https://bugs.php.net/bug.php?id=36795
							$child = $dom->createElement($key, '');
							$child->appendChild(new DOMText($value));
						}
						else
							$child = $dom->createElement($key, $value);

						$node->appendChild($child);
					}
					else
					{
						if ($key[0] === '@')
							$key = Tools::substr($key, 1);

						$attribute = $dom->createAttribute($key);
						$attribute->appendChild($dom->createTextNode($value));
						$node->appendChild($attribute);
					}
				}
				else
				{
					if ($key[0] === '@')
						throw new Exception('Invalid array');

					if (is_numeric(implode('', array_keys($value))))
					{
						foreach ($value as $item)
						{
							$item_data = compact('dom', 'node', 'key', 'format');
							$item_data['value'] = $item;
							self::createChildR($item_data);
						}
					}
					else
						self::createChildR(compact('dom', 'node', 'key', 'value', 'format'));
				}
			}
			else
				throw new Exception('Invalid array');
		}
	}

/**
 * Helper to fromArrayR(). It will create childs of arrays
 *
 * @param array $data Array with informations to create childs
 * @return void
 */
	protected static function createChildR($data)
	{
		$value = null;
		$dom = null;
		$key = null;
		$format = null;
		$node = null;

		extract($data);

		$child_ns = $child_value = null;
		if (is_array($value))
		{
			if (isset($value['@']))
			{
				$child_value = (string)$value['@'];
				unset($value['@']);
			}
			if (isset($value['xmlns:']))
			{
				$child_ns = $value['xmlns:'];
				unset($value['xmlns:']);
			}
		}
		elseif (!empty($value) || $value === 0)
			$child_value = (string)$value;

		$child = $dom->createElement($key);
		if (!is_null($child_value))
			$child->appendChild($dom->createTextNode($child_value));

		if ($child_ns)
			$child->setAttribute('xmlns', $child_ns);

		self::fromArrayR($dom, $child, $value, $format);
		$node->appendChild($child);
	}

/**
 * Returns this XML structure as a array.
 *
 * @param SimpleXMLElement|DOMDocument|DOMNode $obj SimpleXMLElement, DOMDocument or DOMNode instance
 * @return array Array representation of the XML structure.
 * @throws XmlException
 */
	public static function toArray($obj)
	{
		if ($obj instanceof DOMNode)
			$obj = simplexml_import_dom($obj);

		if (!($obj instanceof SimpleXMLElement))
			throw new Exception('The input is not instance of SimpleXMLElement, DOMDocument or DOMNode.');

		$result = array();
		$namespaces = array_merge(array('' => ''), $obj->getNamespaces(true));
		self::toArrayR($obj, $result, '', array_keys($namespaces));
		return $result;
	}

/**
 * Recursive method to toArray
 *
 * @param SimpleXMLElement $xml SimpleXMLElement object
 * @param array $parent_data Parent array with data
 * @param string $ns Namespace of current child
 * @param array $namespaces List of namespaces in XML
 * @return void
 */
	protected static function toArrayR($xml, &$parent_data, $ns, $namespaces)
	{
		$data = array();

		foreach ($namespaces as $namespace)
		{
			foreach ($xml->attributes($namespace, true) as $key => $value)
			{
				if (!empty($namespace))
					$key = $namespace.':'.$key;

				$data['@'.$key] = (string)$value;
			}

			foreach ($xml->children($namespace, true) as $child)
				self::toArrayR($child, $data, $namespace, $namespaces);
		}

		$as_string = trim((string)$xml);
		if (empty($data))
			$data = $as_string;
		elseif (Tools::strlen($as_string) > 0)
			$data['@'] = $as_string;

		if (!empty($ns))
			$ns .= ':';

		$name = $ns.$xml->getName();
		if (isset($parent_data[$name]))
		{
			if (!is_array($parent_data[$name]) || !isset($parent_data[$name][0]))
				$parent_data[$name] = array($parent_data[$name]);

			$parent_data[$name][] = $data;
		}
		else
			$parent_data[$name] = $data;
	}
}
