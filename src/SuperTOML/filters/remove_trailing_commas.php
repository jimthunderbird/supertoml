<?php
return function($content) {
    //remove any pattern like ',  } or ',  ]'
    $content = \preg_replace_callback("/,[\s]?(}|])/", function($matches) {
        return str_replace(",", "", $matches[0]);
    }, $content);

    return $content;
};
