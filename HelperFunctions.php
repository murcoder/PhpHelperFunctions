<?php

namespace App\Helpers;

use Spatie\Color\Rgba;

/**
 * Class ApplicationHelper
 * Provides individual static functions
 *
 * @package App\Helpers
 */
class ApplicationHelper
{
    /**
     * Check if given string is in json format
     *
     * @param string $str
     * @return bool
     */
    public static function isJson(string $str)
    {
        json_decode($str);

        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Check if string is compressed with gzip
     *
     * @param string $str
     * @return bool
     */
    public static function isGzipped(string $str)
    {
        if (mb_strpos($str, "\x1f"."\x8b"."\x08") === 0) {
            return true;
        } elseif (@gzuncompress($str) !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove square bracket from string/s
     *
     * @param string|array $dirty
     * @return string|array : returns the cleaned strings without square brackets
     */
    public static function removeSquareBrackets($dirty)
    {
        $purifiedArr = [];

        if (is_array($dirty)) {
            foreach ($dirty as $value) {
                $purifiedArr[] = preg_replace("/{|}/", "", $value);
            }

            return $purifiedArr;
        }

        if (is_string($dirty)) {
            return preg_replace("/{|}/", "", $dirty);
        }

        return "";
    }

    /**
     * Format string from camelCase to snake_case
     *
     * @param string $camelCase
     * @return string : snake_case_string
     */
    public static function camelCaseToSnakeCase(string $camelCase)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camelCase));
    }

    /**
     * Remove all excaping backslaches out of string
     *
     * @param string $text
     * @return null|string|string[]
     */
    public static function removeEscapeBackslash(string $text)
    {
        $cleaned = str_replace("\\", "", $text);

        return $cleaned;
    }

    /**
     * Checks if HTML contains not allowed content
     *
     * @param string $html : dirty html content
     * @param array $whitelist : allowed tags
     * @return bool : true if valid
     * @throws \App\Exceptions\HtmlValidationException
     */
    public static function validateHtml(string $html, array $whitelist = [])
    {
        try {
            if (! $whitelist) {
                $whitelist = config('purifier.html.allowed.tags');
            }

            $cleanedHtml = ApplicationHelper::removeEscapeBackslash($html);

            /** @var \DOMDocument $dom */
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($cleanedHtml);

            $tags = [];
            foreach ($dom->getElementsByTagName('*') as $element) {
                $tags[] = $element->nodeName;
            }

            $tags = array_unique($tags);

            foreach ($tags as $tag) {
                if (! in_array($tag, $whitelist)) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Remove special characters from string
     *
     * @param string $string : dirty
     * @return string : cleaned
     */
    public static function removeSpecialChars(string $string)
    {
        $string = strip_tags($string);
        $string = str_replace("\n", ' ', $string);
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^\p{L}\p{N}\s-():.!?,;]/u',  '', $string); // Removes special chars.

        return str_replace('-', ' ', $string);
    }

    /**
     * Replace a key in an associative array
     *
     * @param array $arr
     * @param string $oldKey
     * @param string $newKey
     * @return array : associative array with new key
     */
    public static function changeArrayKey(array $arr, string $oldKey, string $newKey)
    {
        $json = str_replace('"'.$oldKey.'":', '"'.$newKey.'":', json_encode($arr));

        return json_decode($json, true);
    }

    /**
     * Flatten multi-dimensional arrays recursively
     *
     * @param array $multidimensionalArray
     * @return array
     */
    public static function flatten(array $multidimensionalArray)
    {
        $flatArray = [];
        $isAssociativeArray = ApplicationHelper::isAssociativeArray($multidimensionalArray);

        foreach ($multidimensionalArray as $key => $value) {
            if (is_array($value)) {
                $result = ApplicationHelper::flatten($value);
                $flatArray = array_merge($flatArray, $result);
            } else {
                if($isAssociativeArray) {
                    $flatArray[$key] = $value;
                } else {
                    // Just append - ignore key
                    $flatArray[] = $value;
                }
            }
        }

        return $flatArray;
    }

    /**
     * Check if given array is an associative array
     *
     * @param array $arr
     * @return bool
     */
    public static function isAssociativeArray(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Generate a random rbga color
     *
     * @param float $alpha
     * @param string|null $color : 'r', 'g', 'b'
     * @return \Spatie\Color\Rgba
     */
    public static function generateRandomRbgColor(float $alpha = 1, string $color = null)
    {
        switch ($color) {
            case 'r':
                return Rgba::fromString('rgba('.mt_rand(0, 255).', 0, 0, '.$alpha.')');
            case 'g':
                return Rgba::fromString('rgba(0, '.mt_rand(0, 255).', 0, '.$alpha.')');
            case 'b':
                return Rgba::fromString('rgba(0, 0, '.mt_rand(0, 255).', '.$alpha.')');
            default:
                return Rgba::fromString('rgba('.mt_rand(0, 255).', '.mt_rand(0, 255).', '.mt_rand(0, 255).', '.$alpha.')');
        }
    }

    /**
     * Slice a text into equal parts
     *
     * @param string $text
     * @param int $lengthOfSinglePart
     * @return array - contains parts of the text with a specific length
     */
    public static function sliceString(string $text, int $lengthOfSinglePart)
    {
        $textParts = [];
        $stringLength = strlen($text);
        $start = 0;

        while ($stringLength > 0) {
            $textParts[] = substr($text, $start, $lengthOfSinglePart);
            $start += $lengthOfSinglePart;
            $stringLength -= $lengthOfSinglePart;
        }

        return $textParts;
    }

    /**
     * @param string $fileName
     * @return bool
     */
    public static function isValidFileName(string $fileName) {
        return preg_match('/^[\/\w\-. ]+$/', $fileName) ? true : false;
    }

    /**
     * Checks if the given file is a valid pdf
     *
     * @param string $pdfContent
     * @return bool : true if the file is a valid pdf
     */
    public static function isValidPdf(string $pdfContent): bool
    {
        $mime_type_found = (new \finfo(FILEINFO_MIME))->buffer($pdfContent);

        return $mime_type_found === 'application/pdf; charset=binary';
    }

    /**
     * Replace german accents like ä, ö and ü
     *
     * @param string $text
     * @return bool|string
     */
    public static function replaceAccentsChars(string $text)
    {
        return iconv('utf-8', 'ascii//TRANSLIT', $text);
    }
}