<?php

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;


class FormatXml
{
    public function __construct()
    {
        if ( ! extension_loaded('dom') ) {
            throw new ExtensionException(
                [ 'Missing PHP extension: dom' ]
            );
        }

        if ( ! extension_loaded('simplexml') ) {
            throw new ExtensionException(
                [ 'Missing PHP extension: simplexml' ]
            );
        }
    }


    /**
     * @return \SimpleXMLElement|Ret<\SimpleXMLElement>
     */
    public function parse_xml_sxe(?array $fallback, $xml)
    {
        $theType = Lib::type();

        if ( ! $theType->string_not_empty($xml)->isOk([ &$xmlStringNotEmpty, &$ret ]) ) {
            return Ret::throw($fallback, $ret);
        }

        if ( false === strpos($xmlStringNotEmpty, '<') ) {
            return Ret::throw(
                $fallback,
                [ 'The `xml` should contain at least one symbol `<`', $xml ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( false
            || (false !== stripos($xml, '<!--'))
            || (false !== stripos($xml, '<![CDATA['))
            || (false !== stripos($xml, '<?xml'))
            || (false !== stripos($xml, '<html'))
            || (false !== stripos($xml, 'Envelope'))
            || (preg_match('/<\w+:(\w+)[^>]*>|xmlns:/i', $xml))
        ) {
            return Ret::throw(
                $fallback,
                [ 'The `xml` cannot be parsed using SimpleXmlElement due to it contains complex XML', $xml ],
                [ __FILE__, __LINE__ ]
            );
        }

        libxml_use_internal_errors(true);

        $sxe = null;
        $e = null;

        try {
            $sxe = new \SimpleXMLElement($xmlStringNotEmpty);
        }
        catch ( \Throwable $e ) {
        }

        $errorsArray = libxml_get_errors();

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if ( [] !== $errorsArray ) {
            $ret = Ret::new();

            $lines = preg_split('/\R/', $xml);

            foreach ( $errorsArray as $error ) {
                $message = rtrim($error->message);
                $message = "[ ERROR ] {$message} at line {$error->line}";

                $line = $lines[($error->line) - 1];
                $line = rtrim($line);

                $ret->addError(
                    [ $message, $line, $error ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( $ret->isFail() ) {
                return Ret::throw($fallback, $ret);
            }
        }

        if ( $e ) {
            return Ret::throw(
                $fallback,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fallback, $sxe);
    }


    /**
     * @return \DOMDocument|Ret<\DOMDocument>
     */
    public function parse_xml_dom_document(?array $fallback, $xml)
    {
        $theType = Lib::type();

        if ( ! $theType->string_not_empty($xml)->isOk([ &$xmlStringNotEmpty, &$ret ]) ) {
            return Ret::throw($fallback, $ret);
        }

        if ( false === strpos($xmlStringNotEmpty, '<') ) {
            return Ret::throw(
                $fallback,
                [ 'The `xml` should contain at least one symbol `<`', $xml ],
                [ __FILE__, __LINE__ ]
            );
        }

        libxml_use_internal_errors(true);

        $ddoc = null;
        $e = null;

        try {
            $ddoc = new \DOMDocument('1.0', 'utf-8');
            $ddoc->loadXML($xmlStringNotEmpty);
        }
        catch ( \Throwable $e ) {
        }

        $errorsArray = libxml_get_errors();

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if ( [] !== $errorsArray ) {
            $ret = Ret::new();

            $lines = preg_split('/\R/', $xml);

            foreach ( $errorsArray as $error ) {
                $message = rtrim($error->message);
                $message = "[ ERROR ] {$message} at line {$error->line}";

                $line = $lines[($error->line) - 1];
                $line = rtrim($line);

                $ret->addError(
                    [ $message, $line, $error ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ( $ret->isFail() ) {
                return Ret::throw($fallback, $ret);
            }
        }

        if ( $e ) {
            return Ret::throw(
                $fallback,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fallback, $ddoc);
    }
}
