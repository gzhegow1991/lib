<?php

namespace Gzhegow\Lib\Modules\Format;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Ret;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;


class FormatXml
{
    public function __construct()
    {
        if (! extension_loaded('dom')) {
            throw new RuntimeException(
                'Missing PHP extension: dom'
            );
        }

        if (! extension_loaded('simplexml')) {
            throw new RuntimeException(
                'Missing PHP extension: simplexml'
            );
        }
    }


    /**
     * @param Ret $ret
     *
     * @return \SimpleXMLElement|mixed
     */
    public function parse_xml_sxe($xml, $ret = null)
    {
        if (! Lib::type()->string_not_empty($xmlString, $xml)) {
            return Result::err(
                $ret,
                [ 'The `xml` should be a non-empty string', $xml ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (false === strpos($xmlString, '<')) {
            return Result::err(
                $ret,
                [ 'The `xml` should contain at least one symbol `<`', $xml ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (false
            || (false !== stripos($xml, '<!--'))
            || (false !== stripos($xml, '<![CDATA['))
            || (false !== stripos($xml, '<?xml'))
            || (false !== stripos($xml, '<html'))
            || (false !== stripos($xml, 'Envelope'))
            || (preg_match('/<\w+:(\w+)[^>]*>|xmlns:/i', $xml))
        ) {
            return Result::err(
                $ret,
                [ 'The `xml` cannot be parsed using SimpleXmlElement due to it contains complex XML', $xml ],
                [ __FILE__, __LINE__ ]
            );
        }

        libxml_use_internal_errors(true);

        $sxe = null;
        $e = null;

        try {
            $sxe = new \SimpleXMLElement($xmlString);
        }
        catch ( \Throwable $e ) {
        }

        $errorsArray = libxml_get_errors();

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if (! empty($errorsArray)) {
            $lines = preg_split('/\R/', $xml);

            foreach ( $errorsArray as $error ) {
                $message = rtrim($error->message);
                $message = "[ ERROR ] {$message} at line {$error->line}";

                $line = $lines[ ($error->line) - 1 ];
                $line = rtrim($line);

                Result::err(
                    $ret,
                    [ $message, $line, $error ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ($ret->isErr()) {
                return Result::pass($ret);
            }
        }

        if ($e) {
            return Result::err(
                $ret,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Result::ok($ret, $sxe);
    }

    /**
     * @param Ret $ret
     *
     * @return \DOMDocument|mixed
     */
    public function parse_xml_dom_document($xml, $ret = null)
    {
        if (! Lib::type()->string_not_empty($xmlString, $xml)) {
            return Result::err(
                $ret,
                [ 'The `xml` should be a non-empty string', $xml ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (false === strpos($xmlString, '<')) {
            return Result::err(
                $ret,
                [ 'The `xml` should contain at least one symbol `<`', $xml ],
                [ __FILE__, __LINE__ ]
            );
        }

        libxml_use_internal_errors(true);

        $ddoc = null;
        $e = null;

        try {
            $ddoc = new \DOMDocument('1.0', 'utf-8');
            $ddoc->loadXML($xmlString);
        }
        catch ( \Throwable $e ) {
        }

        $errorsArray = libxml_get_errors();

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if (! empty($errorsArray)) {
            $lines = preg_split('/\R/', $xml);

            foreach ( $errorsArray as $error ) {
                $message = rtrim($error->message);
                $message = "[ ERROR ] {$message} at line {$error->line}";

                $line = $lines[ ($error->line) - 1 ];
                $line = rtrim($line);

                Result::err(
                    $ret,
                    [ $message, $line, $error ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if ($ret->isErr()) {
                return Result::pass($ret);
            }
        }

        if ($e) {
            return Result::err(
                $ret,
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Result::ok($ret, $ddoc);
    }
}
