<?php
namespace SuperTOML\Tests;

use PHPUnit\Framework\TestCase;
use SuperTOML\TOMLParser;

class TOMLStrTest extends TestCase
{
    public function testSimpleTOML() {
        $toml = <<<TOML
[a.b.c]
key = 1
TOML;
        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml)->toArray();

        $this->assertSame($data['a']['b']['c']['key'], 1);
    }

    public function testMultilineJSON() {
    $toml = <<<TOML
[a.b.c]
json = {
    key1 = 1,
    key2 = 2
}
TOML;
        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml)->toArray();

        $this->assertSame($data['a']['b']['c']['json']['key1'], 1);
    }

    public function testCommentStripping() {
        $toml = <<<TOML
# comment 1
# comment 2
# comment 3
TOML;

        $parser = new TOMLParser();
        $this->assertSame($parser->parseTOMLStr($toml)->getRawContent(), "");
    }

    public function testTrailingComma() {
        $toml = <<<TOML
[section]
keys = {
    subkey1 = 'value1',
    subkey2 = 'value2',
    subkey3 = 'value3',
}
TOML;

        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml)->toArray();
        $this->assertSame($data['section']['keys']['subkey3'], 'value3');

        $toml = <<<TOML
[section]
keys = [
    'value1',
    'value2',
    'value3',]
TOML;

        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml)->toArray();
        $this->assertSame($data['section']['keys'][1], 'value2');
        $this->assertSame($data['section']['keys'][2], 'value3');

        $toml = <<<TOML
[section]
keys = [
    'value1',
    'value2',
    'value3',
]
TOML;

        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml)->toArray();
        $this->assertSame($data['section']['keys'][1], 'value2');
        $this->assertSame($data['section']['keys'][2], 'value3');

    }
}
