<?php
namespace Neodock\Framework;

class StringUtils
{
    public static function TitleCase($title): string
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
}