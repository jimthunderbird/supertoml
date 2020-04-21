<?php
/**
 * convert all patterns like "abc=efg" to "__equal__"
 * convert all patterns like 'abc=efg' to "__equal__"
 */
return function($content) {
    \preg_match_all("/\"[\=\/\?\s0-9a-zA-Z_-]+\"/", $content, $matches);

    foreach($matches[0] as $match) {
        $content = \str_replace($match, \str_replace("=","__equal__", $match), $content);
    }

    \preg_match_all("/\'[\=\/\?\s0-9a-zA-Z_-]+\'/", $content, $matches);

    foreach($matches[0] as $match) {
        $content = \str_replace($match, \str_replace("=","__equal__", $match), $content);
    }


    return $content;
};
