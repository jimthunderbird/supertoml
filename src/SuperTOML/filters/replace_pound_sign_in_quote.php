<?php
use SuperTOML\Symbol;

return function($content) {
    $specialSignsValues = [
        Symbol::POUND_SIGN['value'],
    ];

    $specialSignsReplacements = [
        Symbol::POUND_SIGN['replacement'],
    ];

    \preg_match_all("/\"[\=:#\/\?\s0-9a-zA-Z_-]+\"/", $content, $matches);

    foreach($matches[0] as $match) {
        $content = \str_replace($match, \str_replace($specialSignsValues,$specialSignsReplacements, $match), $content);
    }

    \preg_match_all("/\'[\=:#\/\?\s0-9a-zA-Z_-]+\'/", $content, $matches);

    foreach($matches[0] as $match) {
        $content = \str_replace($match, \str_replace($specialSignsValues,$specialSignsReplacements, $match), $content);
    }

    return $content;
};
