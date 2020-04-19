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
        $data = $parser->parseTOMLStr($toml);

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
        $data = $parser->parseTOMLStr($toml);

        $this->assertSame($data['a']['b']['c']['json']['key1'], 1);

    }
}