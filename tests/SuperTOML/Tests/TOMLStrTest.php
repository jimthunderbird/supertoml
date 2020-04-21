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
'value2', # this is value2
'value3',
]
TOML;

        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml)->toArray();
        $this->assertSame($data['section']['keys'][1], 'value2');
        $this->assertSame($data['section']['keys'][2], 'value3');

        $toml = <<<TOML
[section]
keys = [
'value1',
'value2', # this is value2
'value3','value4',
'value5',
]
TOML;

        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml)->toArray();
        $this->assertSame($data['section']['keys'][3], 'value4');
        $this->assertSame($data['section']['keys'][4], 'value5');
    }

    public function testInlineTable() {
        $toml = <<<TOML
[area]
points = [ { x = 1, y = 2, z = 3 },
           { x = 7, y = 8, z = 9 },
           { x = 2, y = 4, z = 8 } ]
TOML;
        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml)->toArray();
        $this->assertSame($data['area']['points'][0]['x'], 1);
        $this->assertSame($data['area']['points'][1]['y'], 8);
        $this->assertSame($data['area']['points'][2]['y'], 4);
    }

    public function testFilterAddAndRemove() {
        $toml = <<<TOML
#comment
[section1]
TOML;
        $parser = new TOMLParser();
        $content = $parser
            ->removeFilter("remove_comments")
            ->parseTOMLStr($toml)
            ->getRawContent();
        $this->assertTrue(strpos($content, "#") !== FALSE);
    }

    public function testNoSectionKeys() {
        $toml = <<<TOML
title = 'simpletitle'

a = 1
b = 2

[c]
d = 3
e = { f = 4 }

TOML;
        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml)->toArray();

        $this->assertSame($data['title'], 'simpletitle');
        $this->assertSame($data['a'], 1);
        $this->assertSame($data['b'], 2);
        $this->assertSame($data['c']['e']['f'], 4);
    }

    public function testSpecialChars() {
        $toml = <<<TOML
[abc]
links = [
    { link = '/mywebsite?section=123', name = "a = name1", owner = 'abc@xyz.com' },
    { link = "/mywebsite?section=456", name = 'name2' },
    { link = "/mywebsite?section=789", name = "name3" },
]
TOML;
        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml)->toArray();
        $this->assertSame($data['abc']['links'][1]['name'], 'name2');
        $this->assertSame($data['abc']['links'][2]['name'], 'name3');
        $this->assertSame($data['abc']['links'][0]['owner'], 'abc@xyz.com');
    }
}
