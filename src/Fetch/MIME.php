<?php

/*
 * This file is part of the Fetch package.
 *
 * (c) Robert Hafner <tedivm@tedivm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fetch;

/**
 * This library is a wrapper around the Imap library functions included in php.
 *
 * @package Fetch
 * @author  Robert Hafner <tedivm@tedivm.com>
 * @author  Sergey Linnik <linniksa@gmail.com>
 */
final class MIME
{
    /**
     * @param string $text
     * @param string $targetCharset
     *
     * @return string
     */
    public static function decode($text, $targetCharset = 'utf-8')
    {
        if (null === $text) {
            return null;
        }

        /**
         * Decodes filename given in the content-disposition header according
         * to RFC5987, such as filename*=utf-8''filename.png. Note that the
         * language sub-component is defined in RFC5646, and that the filename
         * is URL encoded (in the charset specified)
         *
         * @link https://github.com/osTicket/osTicket-1.7/pull/738
         */
        if (preg_match("/([\w!#$%&+^_`{}~-]+)'([\w-]*)'(.*)$/", $text, $match)) {
            $sourceCharset = str_replace('iso-8859-8-i', 'iso-8859-8', $match[1]);
            return iconv($sourceCharset, $targetCharset . '//IGNORE', urldecode($match[3]));
        }

        $result = '';

        foreach (imap_mime_header_decode($text) as $word) {

            if ($word->charset === 'default') {
                $ch = 'ascii';
            } else if ($word->charset === 'x-unknown') {
                $ch = 'utf-8';
            } else {
                $ch = $sourceCharset = str_replace('iso-8859-8-i', 'iso-8859-8', $word->charset);
            }

            $result .= iconv($ch, $targetCharset . '//IGNORE', $word->text);
        }

        return $result;
    }
}
