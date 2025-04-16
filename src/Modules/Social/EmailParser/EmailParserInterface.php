<?php

namespace Gzhegow\Lib\Modules\Social\EmailParser;

interface EmailParserInterface
{
    public function parseEmail($value, ?array $filters = null, string &$emailDomain = null, string &$emailName = null) : string;

    public function parseEmailFake($value, string &$emailDomain = null, string &$emailName = null) : string;

    public function parseEmailNonFake($value, ?array $filters = null, string &$emailDomain = null, string &$emailName = null) : string;
}
