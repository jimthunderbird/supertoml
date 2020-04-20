<?php
return function($content) {
    //convert multi line json to single line json
    \preg_match_all("/\{(?=.*\n)[^}]+\}\.*(\n}){0,}/", $content, $matches);

    foreach($matches[0] as $match) {
        $content = \str_replace($match, implode(" ", explode("\n", $match)), $content);
    }

    return $content;
};
