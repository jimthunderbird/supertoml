<?php
namespace SuperTOML;

class TOMLParser
{
    private $importFilesKey = "@imports";
    private $tomlFile;
    private $tomlFileDir;
    private $lines = [];
    private $dataMap = [];
    private $filters = [];
    private static $requiredFilters = [];

    private $rawContent = "";

    public function __construct(array $filterNames = [
        'replace_pound_sign_in_quote',
        'remove_comments',
        'encode_special_signs_in_value'
    ]) {
        foreach($filterNames as $filterName) {
            if (!isset(static::$requiredFilters[$filterName])) {
                static::$requiredFilters[$filterName] = require __DIR__."/filters/$filterName.php";
            }
            $this->filters[$filterName] = static::$requiredFilters[$filterName];
        }
    }

    public function parseTOMLFile(string $tomlFile) {
        $this->tomlFileDir = dirname($tomlFile);
        $this->parseTOMLStr(\trim(\file_get_contents($tomlFile)));
        return $this;
    }

    public function parseTOMLStr(string $content) {
        //TOML key allows this pattern A-Za-z0-9_-
        $regexTOMLKey = "|[@a-zA-Z0-9_-]+[^s]=|"; # we also include special sign '@'

        $content = $this->applyFiltersToContent($content);

        $this->rawContent = $content;

        $this->lines = \explode("\n", $content);

        $sectionDataMap = [];

        //first pass, handle the non-section keys (any keys that does not belong to a specific section)
        $defaultSectionName = "___default_section___";
        $section = $defaultSectionName; //we will have a default section first
        foreach($this->lines as $line) {
            $line = \trim($line);
            $length = \strlen($line);
            if (\strlen($line) > 2 //we need to make sure there is at least one character in between the []
                && $line[0] ==='['
                && $line[$length - 1] === ']') { //this is a section
                break;
            } else if (\strlen($line) > 0 && $line[0] !== "[") { //this is not a section
                if (strlen($section) > 0) {
                    $sectionDataMap[$section][] = $line;
                }
            }
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
                $sectionDataMap[$section] = [];
            } else if (\strlen($line) > 0 && $line[0] !== "[") { //this is not a section
                if (strlen($section) > 0) {
                    $sectionDataMap[$section][] = $line;
                }
            }
        }

        foreach($sectionDataMap as $section => $lines) {
            if (\is_array($lines)) {

                //walk through all the lines
                $numOfLines = count($lines);

                for ($i = 0; $i < $numOfLines; $i ++) {
                    $lines[$i] = \str_replace("'",'"', $lines[$i]);
                    $lines[$i] = \preg_replace_callback(
                        $regexTOMLKey,
                        function($matches) {
                            return '"'.\trim(str_replace('=','',$matches[0])).'":';
                        },
                        $lines[$i]
                    );

                    $lineLength = strlen($lines[$i]);
                    if (@$lines[$i + 1] !== null
                        && $lines[$i][$lineLength - 1] != ","
                        && $lines[$i][$lineLength - 1] != "{"
                        && $lines[$i][$lineLength - 1] != "["
                    ) {
                        //add the comma at the end
                        $lines[$i] = $lines[$i] . ",";
                    }
                    $lines[$i] = trim($lines[$i]);
                }

                $value = "{" . implode("", $lines). "}";
                $value = str_replace([",}",",]"], ["}","]"], $value);
                $value = \json_decode($value, true);

                $this->assignArrayByPath($sectionDataMap, $section, $value, ".");
            }

            if (\strpos($section, ".") !== FALSE) {
                unset($sectionDataMap[$section]);
            }
        }

        $defaultSectionDataMap = [];

        if (\array_key_exists($defaultSectionName, $sectionDataMap) === TRUE) {
            if ( \is_array($sectionDataMap[$defaultSectionName]) ) {
                $defaultSectionDataMap = \json_decode(\json_encode($sectionDataMap[$defaultSectionName]), true);
            }
        }

        unset($sectionDataMap[$defaultSectionName]);

        //now we will just merge $nonSectionDataMap and $sectionDataMap
        $this->dataMap = \array_replace_recursive($defaultSectionDataMap, $sectionDataMap);

        $specialSignsValues = [
            Symbol::POUND_SIGN['value'],
            Symbol::EQUAL_SIGN['value'],
            Symbol::COLON_SIGN['value'],
        ];

        $specialSignsReplacements = [
            Symbol::POUND_SIGN['replacement'],
            Symbol::EQUAL_SIGN['replacement'],
            Symbol::COLON_SIGN['replacement'],
        ];

        //we need to decode the special characters like = sign
        $this->dataMap = \json_decode(
            str_replace($specialSignsReplacements, $specialSignsValues, \json_encode($this->dataMap)),
            true
        );

        $tomlsToImport = [];

        //speical treatment for '@import'
        foreach($this->dataMap as $key => $val) {
            if ($key === $this->importFilesKey) {
                $tomlsToImport = $val;
                //we only process the first '@import'
                unset($this->dataMap["@imports"]);
                break;
            }
        }

        $dataMapClone = $this->dataMap;
        $dataMap = [];
        if (count($tomlsToImport) > 0) {
            foreach($tomlsToImport as $tomlToImport) {
                $tomlToImport = $this->tomlFileDir . "/" . $tomlToImport;
                $parser = new self();
                $dataMap = $this->deepMergeArrays($dataMap, $parser->parseTOMLFile($tomlToImport)->toArray());
            }
            $dataMap = $this->deepMergeArrays($dataMap, $dataMapClone);
            $this->dataMap = $dataMap;
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

    public function setImportFilesKey(string $importFilesKey) {
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

    private function deepMergeArrays(array $array1, array $array2) {
        $merged = $array1;

        foreach ($array2 as $key => & $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->deepMergeArrays($merged[$key], $value);
            } else if (is_numeric($key)) {
                if (!in_array($value, $merged)) {
                    $merged[] = $value;
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
