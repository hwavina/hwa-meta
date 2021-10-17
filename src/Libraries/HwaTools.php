<?php

namespace Hwavina\HwaMeta\Libraries;

/**
 * Class HwaTools
 * @package Hwavina\HwaMeta\Libraries
 */
class HwaTools
{
    /**
     * @param $string
     * @return bool
     */
    public static function isJSON($string)
    {
        if ((is_string($string) && is_array(json_decode($string, true)))) {
            $check = true;
        } else {
            $check = false;
        }
        return $check;
    }

    /**
     * @param $url
     * @return bool
     */
    public static function isUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $str
     * @return bool
     */
    public static function isProductHandle($str)
    {
        $re = '/^[A-Za-z0-9™℠®]+(?:-[A-Za-z0-9™℠®]+)*$/';
        preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);

        if ($matches) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $maybeint
     * @return float|int
     */
    public static function absint($maybeint)
    {
        return abs(intval($maybeint));
    }

    /**
     * @param $number
     * @return string
     */
    public static function moneyFormat($number)
    {
        return number_format((float)$number, 2, '.', '');
    }

    /**
     * @param $args
     * @param string $defaults
     * @return array
     */
    public static function parse_args($args, $defaults = '')
    {
        if (is_object($args))
            $r = get_object_vars($args);
        elseif (is_array($args))
            $r =& $args;
        else
            self::parse_str($args, $r);

        if (is_array($defaults))
            return array_merge($defaults, $r);
        return $r;
    }

    /**
     * @param $string
     * @param $array
     */
    public static function parse_str($string, &$array)
    {
        parse_str($string, $array);
        if (get_magic_quotes_gpc())
            $array = self::stripslashes_deep($array);
    }

    /**
     * @param $value
     * @return array
     */
    public static function unslash($value)
    {
        return self::stripslashes_deep($value);
    }

    /**
     * @param $value
     * @return array|mixed
     */
    public static function stripslashes_deep($value)
    {
        return self::map_deep(
            $value,
            function ($value) {
                return is_string($value) ? stripslashes($value) : $value;
            }
        );
    }

    /**
     * Maps a function to all non-iterable elements of an array or an object.
     *
     * This is similar to `array_walk_recursive()` but acts upon objects too.
     *
     * @param mixed $value The array, object, or scalar.
     * @param callable $callback The function to map onto $value.
     * @return mixed The value with the callback applied to all non-arrays and non-objects inside it.
     * @since 4.4.0
     *
     */
    public static function map_deep($value, $callback)
    {
        if (is_array($value)) {
            foreach ($value as $index => $item) {
                $value[$index] = self::map_deep($item, $callback);
            }
        } elseif (is_object($value)) {
            $object_vars = get_object_vars($value);
            foreach ($object_vars as $property_name => $property_value) {
                $value->$property_name = self::map_deep($property_value, $callback);
            }
        } else {
            $value = call_user_func($callback, $value);
        }

        return $value;
    }

    /**
     * Unserialize value only if it was serialized.
     *
     * @param string $original Maybe unserialized original, if is needed.
     * @return mixed Unserialized data can be any type.
     */
    public static function maybe_unserialize($original)
    {
        if (self::is_serialized($original)) // don't attempt to unserialize data that wasn't serialized going in
            return @unserialize($original);
        return $original;
    }

    /**
     * Serialize data, if needed.
     *
     * @param string|array|object $data Data that might be serialized.
     * @return mixed A scalar data
     */
    public static function maybe_serialize($data)
    {
        if (is_array($data) || is_object($data))
            return serialize($data);

        if (self::is_serialized($data, false))
            return serialize($data);

        return $data;
    }

    /**
     * Check value to find if it was serialized.
     *
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * @param string $data Value to check to see if was serialized.
     * @param bool $strict Optional. Whether to be strict about the end of the string. Default true.
     * @return bool False if not serialized and true if it was.
     */
    public static function is_serialized($data, $strict = true)
    {
        // if it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace)
                return false;
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3)
                return false;
            if (false !== $brace && $brace < 4)
                return false;
        }
        $token = $data[0];
        switch ($token) {
            case 's' :
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
                break;
            // or else fall through
            case 'a' :
            case 'O' :
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b' :
            case 'i' :
            case 'd' :
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }
        return false;
    }
}
