<?php

namespace Torch\Util;

class Inflector
{
    /**
     * Lower
     * 
     * @param string $value
     * @return string
     */
    public static function lower(string $value) : string
    {
        return mb_strtolower($value, 'UTF-8');
    }
    
    /**
     * Snake
     * 
     * @param string $value
     * @param string $delimiter
     * @return string
     */
    public static function snake(string $value, 
        string $delimiter = '_') : string
    {
        if (!ctype_lower($value))
        {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'. $delimiter, $value));
        }
        return $value;
    }
    
    /**
     * Kebab
     * 
     * @param string $value
     * @return string
     */
    public static function kebab(string $value) : string
    {
        return static::snake($value, '-');
    }
    
    /**
     * Entity singular
     * 
     * @param string $value
     * @return string
     */
    public static function entitySingular(string $value) : string
    {
        return ucfirst(singular(humanize($value, '-')));
    }
    
    /**
     * Entity plural
     * 
     * @param string $value
     * @return string
     */
    public static function entityPlural($value) : string
    {
        return ucfirst(plural(humanize($value, '-')));
    }
}
