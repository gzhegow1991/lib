<?php

/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules\Format;


class FormatHtml
{
    public function content_strip(string $content) : string
    {
        $contentHtml = $content;

        $placeholderStrip = '<!-- {{ STRIP }} -->';

        if ( false !== strpos($content, $placeholderStrip) ) {
            $placeholderStripRegex = preg_quote($placeholderStrip, '/');
            $placeholderStripRegex = '\s*' . $placeholderStripRegex . '\s*';

            $contentHtml = preg_replace('/' . $placeholderStripRegex . '/', '', $contentHtml);
        }

        return $contentHtml;
    }

    public function content_trim(string $content) : string
    {
        $contentHtml = trim($content);

        $placeholderStrip = '<!-- {{ STRIP }} -->';
        if ( false !== strpos($content, $placeholderStrip) ) {
            $placeholderStripRegex = preg_quote($placeholderStrip, '/');
            $placeholderStripRegex = '\s*' . $placeholderStripRegex . '\s*';

            $contentHtml = preg_replace('/' . $placeholderStripRegex . '/', '', $contentHtml);
        }

        $lines = explode("\n", $contentHtml);
        $lines = array_map('rtrim', $lines);
        foreach ( $lines as $i => $l ) {
            if ( '' === $l ) {
                unset($lines[$i]);
            }
        }
        $lines = array_values($lines);

        $contentHtml = implode("\n", $lines) . "\n";

        return $contentHtml;
    }
}
