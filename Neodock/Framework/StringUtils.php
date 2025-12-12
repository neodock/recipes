<?php
namespace Neodock\Framework;

class StringUtils
{
    public static function TitleCase(string $title): string
    {
        // Our array of 'small words' which shouldn't be capitalised if
        // they aren't the first word. Add your own words to taste.
        $smallwordsarray = array(
                'of','a','the','and','an','or','nor','but','is','if','then','else','when',
                'at','from','by','on','off','for','in','out','over','to','into','with'
        );

        // Split the string into separate words
        $words = explode(' ', $title);

        foreach ($words as $key => $word)
        {
            // If this word is the first, or it's not one of our small words, capitalise it
            // with ucwords().
            if ($key == 0 or !in_array($word, $smallwordsarray))
                $words[$key] = ucwords($word);
        }

        // Join the words back into a string
        return implode(' ', $words);
    }

    public static function CurrentUrlWithoutSort(): string {
        $parts = parse_url($_SERVER['REQUEST_URI']);

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);

            // Remove `sort` if present
            unset($query['sort']);
        }

        // Rebuild query string
        $newQuery = http_build_query($query);

        // Rebuild URL
        $newUrl =
            ($parts['host'] ?? '') .
            ($parts['path'] ?? '') .
            ($newQuery ? '?'.$newQuery : '');

        return $newUrl;
    }

    public static function HTMLSafe(string $text): string {
        return preg_replace("/[^A-Za-z0-9 ]/", " ", $text);
    }
}