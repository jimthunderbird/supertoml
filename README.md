# SuperTOML parser, a simple TOML parser written in PHP

## Usage

### Simple TOML Parsing
```php
$toml = <<<TOML
[a.b.c]
key = 1
TOML;
$parser = new SuperTOML\TOMLParser();
$data = $parser->parseTOMLStr($toml);

$this->assertSame($data['a']['b']['c']['key'], 1);
```
