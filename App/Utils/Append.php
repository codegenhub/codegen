<?php


namespace Codegenhub\App\Utils;

class Append
{
    public static function appendJsonItemsToPath(&$originalObject, $path, $items)
    {
        if (count($path) > 0) {
            $key = $path[0];
            $path = array_slice($path, 1);
            static::appendJsonItemsToPath($originalObject[$key], $path, $items);
        } else {
            $originalObject = array_merge($originalObject, $items);
        }

        return $originalObject;
    }

    public static function appendTextItem($originalText, $mappingName, $text): string
    {
        $contents = '';
        foreach (explode(PHP_EOL, $originalText) as $num => $line) {
            if (strpos($line, $mappingName . ' ') !== false) {
                $contents .= $text;
            }
            $contents .= $line . PHP_EOL;
        }

        return $contents;
    }
}
