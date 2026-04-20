<?php

/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;


class FormatXml
{
    public function __construct()
    {
        $theType = Lib::type();

        $theType->is_extension_loaded('dom')->orThrow();
        $theType->is_extension_loaded('simplexml')->orThrow();
    }


    /**
     * @return Ret<\SimpleXMLElement>|\SimpleXMLElement
     */
    public function parse_xml_sxe($fb, $xml)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($xml);

        if ( ! $ret->isOk([ &$xmlStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( false === strpos($xmlStringNotEmpty, '<') ) {
            return Ret::throw(
                $fb,
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
                $fb,
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

            if ( ! $ret->isOk() ) {
                return Ret::throw(
                    $fb,
                    $ret,
                //
                // > commented, cause: without duplicate
                // [ __FILE__, __LINE__ ]
                // < commented, cause: without duplicate
                );
            }
        }

        if ( $e ) {
            return Ret::throw(
                $fb,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $sxe);
    }


    /**
     * @return Ret<\DOMDocument>|\DOMDocument
     */
    public function parse_xml_dom_document($fb, $xml)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($xml);

        if ( ! $ret->isOk([ &$xmlStringNotEmpty ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( false === strpos($xmlStringNotEmpty, '<') ) {
            return Ret::throw(
                $fb,
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

            if ( ! $ret->isOk() ) {
                return Ret::throw(
                    $fb,
                    $ret,
                //
                // > commented, cause: without duplicate
                // [ __FILE__, __LINE__ ]
                // < commented, cause: without duplicate
                );
            }
        }

        if ( $e ) {
            return Ret::throw(
                $fb,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $ddoc);
    }
}
