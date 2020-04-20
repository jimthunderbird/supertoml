<?php
namespace SuperTOML;

class TOMLParser
{
    private $tomlFile;
    private $lines = [];
    private $dataMap = [];
    private $filters = [];
    private static $requiredFilters = [];

    private $rawContent;

    public function __construct(array $filterNames = [
        'remove_comments',
        'convert_multiline_json_to_singleline_json',
        'convert_multiline_array_to_singleline_array',
        'remove_trailing_commas',
    ]) {
        foreach($filterNames as $filterName) {
            if (!isset(static::$requiredFilters[$filterName])) {
                static::$requiredFilters[$filterName] = require __DIR__."/filters/$filterName.php";
            }
            $this->filters[$filterName] = static::$requiredFilters[$filterName];
        }
    }

    public function parseFile(string $tomlFile) {
        \trim(\file_get_contents($tomlFile)); //get the file content
        return $this;
    }

    public function parseTOMLStr(string $content) {
        $content = $this->applyFiltersToContent($content);

        $this->rawContent = $content;

        $this->lines = \explode("\n", $content);

        //first pass, handle the non-section keys (any keys that does not belong to a specific section)
        $section = "___default_section___"; //we will have a default section first
        foreach($this->lines as $line) {
            $line = \trim($line);
            $length = \strlen($line);
            if (\strlen($line) > 2 //we need to make sure there is at least one character in between the []
                && $line[0] ==='['
                && $line[$length - 1] === ']') { //this is a section
                break;
            } else if (\strlen($line) > 0 && $line[0] !== "[") { //this is not a section
                $line = \str_replace("'",'"', $line);
                //try to match a 'key=' pattern and change it to '"key"='
                //TOML key allows this pattern A-Za-z0-9_-
                $line = \preg_replace_callback("|[a-zA-Z0-9_-]+[^s]=|", function($matches) {
                    return '"'.\trim(str_replace('=','',$matches[0])).'":';
                }, $line);
                if (strlen($section) > 0) {
                    $this->dataMap[$section][] = $line;
                }
            }
        }

        foreach($this->dataMap as $section => $lines) {
            $value = \json_decode('{'. implode(",", $lines). '}', true);
            $this->assignArrayByPath($this->dataMap, $section, $value, ".");
        }

        $numOfNonSectionKeys = 0;
        if (isset($this->dataMap[$section])) {
            $numOfNonSectionKeys = \count($this->dataMap[$section]);
        }
        if ($numOfNonSectionKeys > 0) {
            //this means we have default section keys
            //now assign the default section values back to the data map
            foreach($this->dataMap[$section] as $key => $value) {
                $this->dataMap[$key] = $value;
            }

            //now remove the default section
            unset($this->dataMap[$section]);

            //also, remove all the processed lines so far
            for($i = 0; $i < $numOfNonSectionKeys; $i ++) {
                array_shift($this->lines);
            }
        }

        if (count($this->lines) === 0) { //no more lines to process, just return
            return $this;
        }

        //second pass, handle the section keys
        $section = "";
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
                if (strlen($section) > 0) {
                    $this->dataMap[$section][] = $line;
                }
            }
        }

        foreach($this->dataMap as $section => $lines) {
            if (\is_array($lines)) {
                $value = \json_decode('{'. implode(",", $lines). '}', true);
                $this->assignArrayByPath($this->dataMap, $section, $value, ".");
            }

            if (\strpos($section, ".") !== FALSE) {
                unset($this->dataMap[$section]);
            }
        }

        return $this;
    }

    public function addFilter(string $filterName, Closure $filter) {
        $this->filters[$filterName] = $filter;
        return $this;
    }

    public function removeFilter(string $filterName) {
        unset($this->filters[$filterName]);
        return $this;
    }

    public function toArray() : array {
        return $this->dataMap;
    }

    public function toJSON() : string {
        return \json_encode($this->dataMap);
    }

    public function getRawContent() : string {
        return $this->rawContent;
    }

    private function assignArrayByPath(array &$arr, string $path, $value, string $separator='.') : void {
        $keys = explode($separator, $path);

        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }

    private function applyFiltersToContent(string $content) : string {
        foreach($this->filters as $filter) {
            $content = $filter($content);
        }
        return $content;
    }
}
