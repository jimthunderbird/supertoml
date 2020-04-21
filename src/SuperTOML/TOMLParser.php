<?php
namespace SuperTOML;

class TOMLParser
{
    private $tomlFile;
    private $lines = [];
    private $dataMap = [];
    private $filters = [];
    private static $requiredFilters = [];

    private $rawContent = "";

    public function __construct(array $filterNames = [
        'remove_comments',
        'encode_equal_signs_in_value',
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
        $this->parseTOMLStr(\trim(\file_get_contents($tomlFile)));
        return $this;
    }

    public function parseTOMLStr(string $content) {
        //TOML key allows this pattern A-Za-z0-9_-
        $regexTOMLKey = "|[a-zA-Z0-9_-]+[^s]=|";

        $content = $this->applyFiltersToContent($content);

        $this->rawContent = $content;

        $this->lines = \explode("\n", $content);

        //first pass, handle the non-section keys (any keys that does not belong to a specific section)
        $nonSectionDataMap = [];

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
                $line = \preg_replace_callback($regexTOMLKey, $this->getTOMLKeyMatchHandler(), $line);
                if (strlen($section) > 0) {
                    $nonSectionDataMap[$section][] = $line;
                }
            }
        }

        foreach($nonSectionDataMap as $section => $lines) {
            if (\is_array($lines)) {
                $value = \json_decode('{'. implode(",", $lines). '}', true);
                $this->assignArrayByPath($nonSectionDataMap, $section, $value, ".");
            }
        }

        $numOfNonSectionKeys = 0;
        if (isset($nonSectionDataMap[$section])) {
            $numOfNonSectionKeys = \count($nonSectionDataMap[$section]);
        }
        if ($numOfNonSectionKeys > 0) {
            //this means we have default section keys
            //now assign the default section values back to the data map
            foreach($nonSectionDataMap[$section] as $key => $value) {
                $nonSectionDataMap[$key] = $value;
            }

            //now remove the default section
            unset($nonSectionDataMap[$section]);

            //also, remove all the processed lines so far
            for($i = 0; $i < $numOfNonSectionKeys; $i ++) {
                \array_shift($this->lines);
            }
        }

        //second pass, handle the section keys
        $sectionDataMap = [];
        $section = "";
        foreach($this->lines as $line) {
            $line = \trim($line);
            $length = \strlen($line);
            if (\strlen($line) > 2 //we need to make sure there is at least one character in between the []
                && $line[0] ==='['
                && $line[$length - 1] === ']') { //this is a section
                $section = \trim(substr($line, 1, $length - 2));
                $sectionDataMap[$section] = [];
            } else if (\strlen($line) > 0 && $line[0] !== "[") { //this is not a section
                $line = \str_replace("'",'"', $line);
                //try to match a 'key=' pattern and change it to '"key"='
                $line = \preg_replace_callback($regexTOMLKey, $this->getTOMLKeyMatchHandler(), $line);
                if (strlen($section) > 0) {
                    $sectionDataMap[$section][] = $line;
                }
            }
        }

        foreach($sectionDataMap as $section => $lines) {
            if (\is_array($lines)) {
                $value = \json_decode('{'. implode(",", $lines). '}', true);
                $this->assignArrayByPath($sectionDataMap, $section, $value, ".");
            }

            if (\strpos($section, ".") !== FALSE) {
                unset($sectionDataMap[$section]);
            }
        }

        //now we will just merge $nonSectionDataMap and $sectionDataMap
        $this->dataMap = \array_merge_recursive($nonSectionDataMap, $sectionDataMap);

        //we need to decode the special characters like = sign
        $this->dataMap = \json_decode(
            str_replace(Symbol::EQUAL_SIGN['replacement'],Symbol::EQUAL_SIGN['value'], \json_encode($this->dataMap)),
            true
        );

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

    private function getTOMLKeyMatchHandler() {
        return function($matches) {
            return '"'.\trim(str_replace('=','',$matches[0])).'":';
        };
    }

    private function assignArrayByPath(array &$arr, string $path, $value, string $separator='.') : void {
        $keys = \explode($separator, $path);

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
