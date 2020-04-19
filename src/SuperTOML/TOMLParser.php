<?php
namespace SuperTOML;

class TOMLParser
{
    private string $tomlFile;
    private array $lines = [];
    private array $dataMap = [];

    public function parseFile(string $tomlFile) {
        $content = \trim(\file_get_contents($tomlFile)); //get the file content
        return $this->parseTOMLStr($content);
    }

    public function parseTOMLStr(string $content) {
        $content = $this->removeComments($content);

        $content = $this->convertMultilineJSON($content);

        $content = $this->convertArray($content);

        $this->lines = \explode("\n", $content);
        foreach($this->lines as $line) {
            $line = \trim($line);
            $length = \strlen($line);
            if (\strlen($line) > 2 //we need to make sure there is at least one character in between the []
                && $line[0] ==='['
                && $line[$length - 1] === ']') { //this is a section
                $section = \trim(substr($line, 1, $length - 2));
                $this->dataMap[$section] = [];
            } else if (\strlen($line) > 0 && $line[0] !== "[") { //this is not a section
                $line = \str_replace("'",'"', $line);
                //try to match a 'key=' pattern and change it to '"key"='
                //TOML key allows this pattern A-Za-z0-9_-
                $line = \preg_replace_callback("|[a-zA-Z0-9_-]+[^s]=|", function($matches) {
                    return '"'.\trim(str_replace('=','',$matches[0])).'":';
                }, $line);
                $this->dataMap[$section][] = $line;
            }
        }

        foreach($this->dataMap as $section => $lines) {
            $value = \json_decode('{'. implode(",", $lines). '}', true);
            $this->assignArrayByPath($this->dataMap, $section, $value, ".");

            if (\strpos($section, ".") !== FALSE) {
                unset($this->dataMap[$section]);
            }
        }

        return $this->dataMap;
    }

    private function assignArrayByPath(&$arr, $path, $value, $separator='.') {
        $keys = explode($separator, $path);

        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }

    private function removeComments($content) {
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
    }

    private function convertMultilineJSON($content) {
        //convert multi line json to single line json
        \preg_match_all("/\{(?=.*\n)[^}]+\}\.*(\n}){0,}/", $content, $matches);

        foreach($matches[0] as $match) {
            $content = \str_replace($match, implode(" ", explode("\n", $match)), $content);
        }

        return $content;
    }

    private function convertArray($content) {
        //convert multi line array into single line array
        \preg_match_all("/\[(?=.*\n)[^]]+\]\.*(\n]){0,}/", $content, $matches);

        foreach($matches[0] as $match) {
            $content = \str_replace($match, implode(" ", \explode("\n", $match)), $content);
        }

        return $content;
    }
}
