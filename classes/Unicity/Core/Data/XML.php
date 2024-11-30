<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2011-2012 Spadefoot Team
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Unicity\Core\Data;

use Unicity\Config;
use Unicity\Core;
use Unicity\IO;
use Unicity\Lexer;
use Unicity\Throwable;

/**
 * This class represent an XML document.
 *
 * @access public
 * @class
 * @package Core
 */
class XML extends \SimpleXMLElement implements Core\IObject, \JsonSerializable
{
    /**
     * Regex for identifying invalid characters in XML:
     *
     * XML specification defines a strict set of rules for valid characters:
     * - Control characters allowed: Tab (U+0009), Line Feed (U+000A), Carriage Return (U+000D).
     * - Printable characters allowed:
     *   - U+0020 (Space) through U+D7FF.
     *   - U+E000 through U+FFFD.
     *   - Characters beyond U+10000 are allowed but not covered here.
     *
     * This regex identifies characters outside these ranges, which are considered invalid
     * in XML documents and may result in fatal parsing errors.
     *
     * Explanation of the regex:
     * - `\x{0009}`: Matches Tab (U+0009).
     * - `\x{000a}`: Matches Line Feed (U+000A).
     * - `\x{000d}`: Matches Carriage Return (U+000D).
     * - `\x{0020}-\x{D7FF}`: Matches printable characters from U+0020 (Space) to U+D7FF.
     * - `\x{E000}-\x{FFFD}`: Matches additional printable characters from U+E000 to U+FFFD.
     * - The `^` at the start of the regex negates these ranges, matching all invalid characters.
     * - The `/u` flag ensures the regex operates in UTF-8 mode.
     *
     * Note: This regex and the functionality below are tightly coupled with how PHP's regex engine
     *   handles Unicode characters. This may need to be adjusted if the behavior changes in future PHP versions.
     */
    public const XML_INVALID_CHARS_REGEX = '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u';

    /**
     * This variable stores the file name to be used when imported/exported.
     *
     * @access protected
     * @var string the file name to be used
     */
    protected $fileName; // TODO add property set/get accessors

    /**
     * This method adds a CDATA section as a child node.
     *
     * @access public
     * @param string $value the value to be wrapped as CDATA
     * @return \DOMNode the newly created CDATA node
     */
    public function addCData($value)
    {
        $node = dom_import_simplexml($this);
        $child = $node->appendChild($node->ownerDocument->createCDATASection($value));

        return $child;
    }

    /**
     * This method nicely writes out information about the object.
     *
     * @access public
     */
    public function __debug(): void
    {
        var_dump($this);
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        unset($this->fileName);
    }

    /**
     * This method display the data.
     *
     * @access public
     * @param Core\IMessage $message the message container
     */
    public function display(Core\IMessage $message = null): void
    {
        $charset = $this->getEncoding();

        if ($message === null) {
            $message = new Core\Message();
            $send = true;
        } else {
            $send = false;
        }

        $buffer = new IO\StringBuffer($this->asXML());

        $message->setHeader('content-disposition', 'inline');
        $message->setHeader('content-type', 'text/xml; charset=' . $charset);

        $message->setBody($buffer);

        if ($send) {
            $message->send();
        }
    }

    /**
     * This method evaluates whether the specified objects is equal to the current object.
     *
     * @access public
     * @param mixed $object the object to be evaluated
     * @return boolean whether the specified object is equal
     *                 to the current object
     */
    public function __equals($object)
    {
        return (($object !== null) && ($object instanceof Core\Data\XML) && ((string) serialize($object) == (string) serialize($this)));
    }

    /**
     * This method exports the data.
     *
     * @access public
     * @param Core\IMessage $message the message container
     */
    public function export(Core\IMessage $message = null): void
    {
        if (empty($this->file_name)) {
            date_default_timezone_set('America/Denver');
            $this->file_name = date('YmdHis') . '.xml';
        }
        $uri = preg_split('!(\?.*|/)!', $this->file_name, -1, PREG_SPLIT_NO_EMPTY);
        $uri = $uri[count($uri) - 1];

        $charset = $this->getEncoding();

        if ($message === null) {
            $message = new Core\Message();
            $send = true;
        } else {
            $send = false;
        }

        $buffer = new IO\StringBuffer($this->asXML());

        $message->setHeader('content-disposition', 'attachment; filename="' . $uri . '"');
        $message->setHeader('content-type', 'text/xml; charset=' . $charset);

        $message->setBody($buffer);

        if ($send) {
            $message->send();
        }
    }

    /**
     * This method returns the name of the called class.
     *
     * @access public
     * @return string the name of the called class
     */
    public function __getClass(): string
    {
        return get_called_class();
    }

    /**
     * This method returns the character set encoding.
     *
     * @access public
     * @return string the character encoding being used
     */
    public function getEncoding(): string
    {
        $encoding = dom_import_simplexml($this)->ownerDocument->encoding;
        if (!is_string($encoding)) {
            $encoding = Core\Data\Charset::UTF_8_ENCODING;
        }

        return $encoding;
    }

    /**
     * This method returns the specified processing instruction.
     *
     * @access public
     * @param string $target the target name of the processing
     *                       instruction
     * @param integer $index the index of the processing instruction
     * @return array the data associated with the target
     * @throws Throwable\Parse\Exception indicates that an invalid token was
     *                                   encountered
     *
     * @see http://msdn.microsoft.com/en-us/library/ms256173%28v=vs.110%29.aspx
     * @see http://www.w3schools.com/xsl/el_processing-instruction.asp
     * @see http://pastebin.com/x25seJPS
     * @see https://github.com/petertornstrand/tornstrand.com/blob/master/_posts/2008-10-21-reading-xml-processing-instruction-with-php.html
     * @see http://java2s.com/Tutorials/PHP/XML_Functions/PHP_xml_set_processing_instruction_handler_Function.htm
     * @see http://www.xml.com/pub/a/2000/09/13/xslt/
     */
    public function getProcessingInstruction(string $target, int $index = 1): array
    {
        $document = dom_import_simplexml($this)->ownerDocument;
        $xpath = new \DOMXPath($document);
        $instruction = trim($xpath->evaluate("string(//processing-instruction(\"{$target}\")[{$index}])"));

        $directives = [];

        $scanner = new Lexer\Scanner(new IO\StringReader($instruction));
        $scanner->addRule(new Lexer\Scanner\TokenRule\Symbol('='));
        $scanner->addRule(new Lexer\Scanner\TokenRule\Keyword());
        $scanner->addRule(new Lexer\Scanner\TokenRule\Literal('"'));
        $scanner->addRule(new Lexer\Scanner\TokenRule\Whitespace());

        $state = 0;
        $key = null;
        while ($scanner->next()) {
            $tuple = $scanner->current();
            if (Lexer\Scanner\TokenType::identifier()->__equals($tuple->type) && ($state == 0)) {
                $state = 1;
                $key = $tuple->token->__toString();
            } elseif (Lexer\Scanner\TokenType::symbol()->__equals($tuple->type) && ($state == 1)) {
                $state = 2;
            } elseif (Lexer\Scanner\TokenType::literal()->__equals($tuple->type) && ($state == 2)) {
                $state = 3;
                $directives[$key] = $tuple->token->substring(1, $tuple->token->length() - 1)->__toString();
            } elseif (Lexer\Scanner\TokenType::whitespace()->__equals($tuple->type)) {
                $state = 0;
            } else {
                throw new Throwable\Parse\Exception('Unable to parse processing instruction. Invalid token ":token" encountered with type ":type".', [':token' => $tuple->token, ':type' => $tuple->type]);
            }
        }

        return $directives;
    }

    /**
     * This method returns the current object's hash code.
     *
     * @access public
     * @return string the current object's hash code
     */
    public function __hashCode(): string
    {
        return spl_object_hash($this);
    }

    /**
     * This method will remove the current node from its parent.
     *
     * @access public
     */
    public function removeFromParent(): void
    {
        $child = dom_import_simplexml($this);
        $child->parentNode->removeChild($child);
    }

    /**
     * This method returns the current object as a serialized string.
     *
     * @access public
     * @return string a serialized string representing
     *                the current object
     */
    public function __toString()
    {
        return $this->asXML();
    }

    /**
     * This method converts XML to a JSON string.
     *
     * @access protected
     * @return string the JSON encoded string
     *
     * @see https://lostechies.com/seanbiefeld/2011/10/21/simple-xml-to-json-with-php/
     */
    public function jsonSerialize()
    {
        $reader = new Config\XML\Reader(new IO\StringBuffer($this->asXML()));
        $writer = new Config\JSON\Writer($reader->read());

        return $writer->render();
    }

    /**
     * This method returns a standard XML version 1.0 declaration.
     *
     * @access public
     * @static
     * @param string $encoding the XML encoding
     * @param boolean $standalone whether the XML is considered
     *                            to be standalone
     * @return string the XML declaration
     */
    public static function declaration(string $encoding = 'UTF-8', bool $standalone = false): string
    {
        $encoding = strtoupper($encoding);
        $standalone = ($standalone) ? 'yes' : 'no';
        $declaration = "<?xml version=\"1.0\" encoding=\"{$encoding}\" standalone=\"{$standalone}\"?>";

        return $declaration;
    }

    /**
     * This method converts an associated array to either a SimpleXMLElement or an XML formatted
     * string depending on the second parameter.
     *
     * @access public
     * @static
     * @param array $array the associated array to be converted
     * @param boolean $as_string whether to return a string
     * @return mixed either a SimpleXMLElement or an XML
     *               formatted string
     */
    public static function encode(array $array, $as_string = false)
    {
        $writer = new Config\JSON\Writer($array);
        $contents = $writer->render();
        if ($as_string) {
            return $contents;
        }
        $XML = new static($contents);

        return $XML;
    }

    /**
     * This method converts any special characters in a string to XML safe-entities.
     *
     * @access public
     * @static
     * @param string $string the string to be modified
     * @return string the modified string
     */
    public static function entities(string $string): string
    {
        $flags = (defined('ENT_XML1')) ? ENT_QUOTES | ENT_XML1 : ENT_QUOTES;

        $string = html_entity_decode(stripslashes($string), $flags, Core\Data\Charset::UTF_8_ENCODING); // prevents double-escaping

        if (extension_loaded('mbstring')) {
            return array_reduce(preg_split('/(?<!^)(?!$)/u', $string), function ($buffer, $mb_char) {
                $ordinal = ord($mb_char);

                if ((strlen($mb_char) > 1) || (($ordinal < 32) || ($ordinal > 126)) || (($ordinal > 33) && ($ordinal < 40)) || (($ordinal > 59) && ($ordinal < 63))) {
                    $mb_char = mb_encode_numericentity($mb_char, [0x0, 0xffff, 0, 0xffff], Core\Data\Charset::UTF_8_ENCODING);
                }

                $buffer .= $mb_char;

                return $buffer;
            }, '');
        }

        return htmlentities($string, $flags, Core\Data\Charset::UTF_8_ENCODING);
    }

    /**
     * This method returns an instance of the class with the contents of the specified XML file.
     *
     * @access public
     * @static
     * @param IO\File $file the input file stream to be used
     * @return Core\Data\XML an instance of this class
     * @throws Throwable\InvalidArgument\Exception indicates a data type mismatch
     * @throws Throwable\FileNotFound\Exception indicates that the file does not exist
     * @throws \RuntimeException indicates a failure to parse XML
     */
    public static function load(IO\File $file, bool $removeInvalid = true, string $replacement = '�'): Core\Data\XML
    {
        if (!$file->exists()) {
            throw new Throwable\FileNotFound\Exception(
                'Unable to locate file. File does not exist.'
            );
        }

        $buffer = file_get_contents($file);

        // Remove UTF-8 BOM
        $buffer = preg_replace('/^\xEF\xBB\xBF/', '', $buffer);

        // Ensure XML declaration exists
        if (!preg_match('/^<\?xml\s+[^?>]+\?>/', $buffer)) {
            $buffer = static::declaration(Core\Data\Charset::UTF_8_ENCODING) . "\n" . $buffer;
        }

        // Detect invalid characters
        $invalidChars = self::detectInvalidXmlChars($buffer);

        if (!empty($invalidChars)) {
            if ($removeInvalid) {
                // Log out the invalid characters and throw an exception
                foreach ($invalidChars as $charDetails) {
                    error_log(sprintf(
                        'Invalid character detected: "%s" (U+%04X) at position %d in field "%s". Replaced with "%s".',
                        $charDetails['character'],
                        $charDetails['codepoint'],
                        $charDetails['position'],
                        $charDetails['context'],
                        $replacement
                    ));
                }

                $buffer = self::sanitizeXmlBuffer($buffer, $replacement);
            } else {
                throw new \RuntimeException(sprintf(
                    'Invalid characters detected in XML. Details: %s',
                    json_encode([
                        'invalidChars' => $invalidChars,
                    ])
                ));
            }
        }

        // enable xml parsing errors so that we can catch them
        libxml_use_internal_errors(true);

        try {
            $xml = new static($buffer);
        } catch (\Exception $e) {
            // aggregate libxml errors into an error string
            $libxmlErrors = '';
            foreach (libxml_get_errors() as $error) {
                $libxmlErrors .= sprintf(
                    'XML Error: %s at line %d, column %d; ',
                    trim($error->message),
                    $error->line,
                    $error->column
                );
            }
            libxml_clear_errors();

            throw new \RuntimeException(sprintf(
                'Failed to parse XML. Error: %s. XML Errors: %s',
                $e->getMessage(),
                $libxmlErrors
            ), 0, $e);
        }

        // reset xml-parsing error handling
        libxml_use_internal_errors(false);

        return $xml;
    }

    /**
     * This method returns the transformed XML.
     *
     * @access public
     * @static
     * @param \SimpleXMLElement $xml the xml to be transformed
     * @param \SimpleXMLElement $xsl the xsl to be used
     * @return Core\Data\XML an instance of this class
     */
    public static function transform(\SimpleXMLElement $xml, \SimpleXMLElement $xsl): Core\Data\XML
    {
        $processor = new \XSLTProcessor();
        $processor->importStylesheet($xsl);

        return new static($processor->transformToXml($xml));
    }

    /**
     * This method encodes a string using unicode.
     *
     * @access public
     * @param string $string the string to be encoded
     * @return string the encoded string
     */
    public static function toUnicodeString(string $string): string
    {
        $decbytes = static::utf8_to_codepoints($string);
        $value = implode('', array_map(function ($decbyte) {
            return pack('c', $decbyte);
        }, $decbytes));

        return $value;
    }

    /**
     * This method converts a UTF-8 character to its respective codepoint.
     *
     * @access protected
     * @param string $string the UTF-8 string to be converted
     * @return array the unicode codepoint(s)
     */
    protected static function utf8_to_codepoints(string $string): array
    {
        $unicode = [];
        $values = [];
        $lookingFor = 1;
        for ($i = 0; $i < strlen($string); $i++) {
            $ordinal = ord($string[$i]);
            if ($ordinal < 128) {
                $unicode[] = $ordinal;
            } else {
                if (count($values) == 0) {
                    $lookingFor = ($ordinal < 224) ? 2 : 3;
                }
                $values[] = $ordinal;
                if (count($values) == $lookingFor) {
                    $unicode[] = ($lookingFor == 3)
                        ? (($values[0] % 16) * 4096) + (($values[1] % 64) * 64) + ($values[2] % 64)
                        : (($values[0] % 32) * 64) + ($values[1] % 64);
                    $values = [];
                    $lookingFor = 1;
                }
            }
        }

        return $unicode;
    }

    /**
     * This method returns the first value associated with the specified object.
     *
     * @access public
     * @static
     * @param mixed $value the object to be processed
     * @param string $source_encoding the source encoding
     * @param string $target_encoding the target encoding
     * @return mixed the value that was wrapped by
     *               the object
     */
    public static function valueOf($value, string $source_encoding = 'UTF-8', string $target_encoding = 'UTF-8')
    {
        $flags = (defined('ENT_XML1')) ? ENT_QUOTES | ENT_XML1 : ENT_QUOTES;
        if (is_array($value) || is_object($value)) {
            $array = (array)$value;
            if (isset($array[0])) {
                $buffer = $array[0];
                if (is_string($buffer)) {
                    $buffer = Core\Data\Charset::encode($buffer, $source_encoding, $target_encoding);
                }
                $buffer = html_entity_decode($buffer, $flags, $target_encoding);

                return $buffer;
            }
        }
        $buffer = $value;
        if (is_string($buffer)) {
            $buffer = Core\Data\Charset::encode($buffer, $source_encoding, $target_encoding);
        }
        $buffer = html_entity_decode($buffer, $flags, $target_encoding);

        return $buffer;
    }

    /**
     * Polyfill for mb_ord for PHP < 7.2.
     * Returns the Unicode code point of a character.
     *
     * @param string $char The character to convert.
     * @param string $encoding The character encoding (default: UTF-8).
     * @return int|null The Unicode code point or null on failure.
     */
    public static function mb_ord($char, $encoding = 'UTF-8')
    {
        // Use mb_ord if available use it
        if (function_exists('mb_ord')) {
            return mb_ord($char, $encoding);
        }

        if (strlen($char) === 1) {
            return ord($char); // For single-byte characters
        }
        $result = unpack('N', mb_convert_encoding($char, 'UCS-4BE', $encoding));

        return $result ? $result[1] : null;
    }

    /**
     * Detects invalid characters in an XML string buffer based on the XML specification.
     *
     * This function scans the buffer for invalid characters and returns their details,
     * including the character, its Unicode code point, and its position in the buffer.
     *
     * @param string $buffer The XML string buffer to analyze.
     * @return array An array of invalid characters, each represented as an associative array with:
     *               - 'char': The invalid character.
     *               - 'codepoint': The Unicode code point of the character.
     *               - 'offset': The position of the character in the buffer.
     */
    public static function detectInvalidXmlChars(string $buffer): array
    {
        $invalidChars = [];
        if (preg_match_all(self::XML_INVALID_CHARS_REGEX, $buffer, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as [$char, $offset]) {
                $invalidChars[] = [
                    'char' => $char,
                    'codepoint' => self::mb_ord($char, 'UTF-8'), // TODO after upgrading php version use mb_ord
                    'offset' => $offset,
                ];
            }
        }

        return $invalidChars;
    }

    /**
     * Removes or replaces invalid XML characters in a string buffer.
     *
     * XML has strict character validity rules, and characters outside the allowed ranges
     * can cause parsing errors. This function ensures the buffer complies with the XML
     * specification by either removing invalid characters or replacing them with a specified value.
     *
     * By default, invalid characters are removed. Optionally, a replacement string can be provided
     * to substitute for invalid characters.
     *
     * @param string $buffer The XML string buffer to sanitize.
     * @param string $replacement The string to replace invalid characters with. Defaults to an empty string.
     * @return string The sanitized XML buffer.
     */
    public static function sanitizeXmlBuffer(string $buffer, string $replacement = ''): string
    {
        return preg_replace(self::XML_INVALID_CHARS_REGEX, $replacement, $buffer);
    }
}
