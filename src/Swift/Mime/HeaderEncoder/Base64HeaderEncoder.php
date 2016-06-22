<?php
namespace XeArts\Eccube\Swift\Mime\HeaderEncoder;

class Base64HeaderEncoder extends \Swift_Encoder_Base64Encoder implements \Swift_Mime_HeaderEncoder
{
    /**
     * Get the name of this encoding scheme.
     * Returns the string 'B'.
     *
     * @return string
     */
    public function getName()
    {
        return 'B';
    }

    /**
     * Takes an unencoded string and produces a Base64 encoded string from it.
     *
     * If the charset is iso-2022-jp, it uses mb_encode_mimeheader instead of
     * default encodeString, otherwise pass to the parent method.
     *
     * @param string $string          string to encode
     * @param int    $firstLineOffset
     * @param int    $maxLineLength   optional, 0 indicates the default of 76 bytes
     * @param string $charset
     *
     * @return string
     */
    public function encodeString($string, $firstLineOffset = 0, $maxLineLength = 0, $charset = 'utf-8')
    {
        if (strtolower($charset) === 'iso-2022-jp') {
            $old = mb_internal_encoding();
            mb_internal_encoding('utf-8');
            $newstring = mb_encode_mimeheader($string, $charset, $this->getName(), "\r\n");
//            $newstring = mb_encode_mimeheader($string, 'JIS', $this->getName(), "\n");
//            $newstring = str_replace(array("\r\n", "\r"), "\n", $newstring);
            mb_internal_encoding($old);

            return $newstring;
        }

        return parent::encodeString($string, $firstLineOffset, $maxLineLength);
    }
}
