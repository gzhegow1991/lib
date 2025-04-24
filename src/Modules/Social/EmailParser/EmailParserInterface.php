<?php

namespace Gzhegow\Lib\Modules\Social\EmailParser;


interface EmailParserInterface
{
    /**
     * @return static
     */
    public function setEmailFakeRegexes(?array $regexList);

    /**
     * @return static
     */
    public function addEmailFakeRegexes(array $regexList);


    public function parseEmail(
        $value, ?array $filters = null,
        ?string &$emailDomain = null, ?string &$emailName = null
    ) : string;

    public function parseEmailFake(
        $value,
        ?string &$emailDomain = null, ?string &$emailName = null
    ) : string;

    public function parseEmailNonFake(
        $value, ?array $filters = null,
        ?string &$emailDomain = null, ?string &$emailName = null
    ) : string;
}
