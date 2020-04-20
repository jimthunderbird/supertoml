<?php
return function($content) {
    //remove all comments
    $lines = \explode("\n", $content);
    $lines = \array_map(function($match) {
        return \preg_replace("/\/\*[\s\S]*?\*\/|([^:]|^)\#.*|([^:]|^)\/\/.*/", "", $match);
    }, $lines);

    $lines = \array_filter($lines, function($line) {
        return \trim($line) !== "";
    });

    $content = \implode("\n",$lines);

    return $content;
};
