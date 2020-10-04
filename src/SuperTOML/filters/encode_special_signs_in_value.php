<?php
/**
 * convert all patterns like "abc=efg" to "__equal__"
 * convert all patterns like 'abc=efg' to "__equal__"
 * convert all patterns like "xx:xxxxxx" to "__colon__"
 */
use SuperTOML\Symbol;

return function($content) {

    $specialSignsValues = [
        Symbol::EQUAL_SIGN['value'],
        Symbol::COLON_SIGN['value'],
    ];

    $specialSignsReplacements = [
        Symbol::EQUAL_SIGN['replacement'],
        Symbol::COLON_SIGN['replacement'],
    ];

    \preg_match_all("/\"[\=:\/\?\s0-9a-zA-Z_-]+\"/", $content, $matches);

    foreach($matches[0] as $match) {
        $content = \str_replace($match, \str_replace($specialSignsValues,$specialSignsReplacements, $match), $content);
    }

    \preg_match_all("/\'[\=:\/\?\s0-9a-zA-Z_-]+\'/", $content, $matches);

    foreach($matches[0] as $match) {
        $content = \str_replace($match, \str_replace(Symbol::EQUAL_SIGN['value'],Symbol::EQUAL_SIGN['replacement'], $match), $content);
    }

    return $content;
};
