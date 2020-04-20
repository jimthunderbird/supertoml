<?php
return function($content) {
    //convert multi line array into single line array
    \preg_match_all("/\[(?=.*\n)[^]]+\]\.*(\n]){0,}/", $content, $matches);

    foreach($matches[0] as $match) {
        $content = \str_replace($match, implode(" ", \explode("\n", $match)), $content);
    }

    return $content;
};
