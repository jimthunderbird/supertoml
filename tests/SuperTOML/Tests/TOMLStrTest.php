<?php
namespace SuperTOML\Tests;

use PHPUnit\Framework\TestCase;
use SuperTOML\TOMLParser;

class TOMLStrTest extends TestCase
{
    //public function testSimpleTOML() {
        //////////////////////$toml = <<<TOML
//////////////////////[a.b.c]
//////////////////////key = 1
//////////////////////TOML;
        //////////////////////$parser = new TOMLParser();
        //////////////////////$data = $parser->parseTOMLStr($toml)->toArray();

        //////////////////////$this->assertSame($data['a']['b']['c']['key'], 1);
    //////////////////////}

    ////////////////////////////public function testMultilineJSON() {
        ////////////////////////////$toml = <<<TOML
////////////////////////////[a.b.c]
////////////////////////////json = {
////////////////////////////key1 = 1,
////////////////////////////key2 = 2
////////////////////////////}
////////////////////////////TOML;
        ////////////////////////////$parser = new TOMLParser();
        ////////////////////////////$data = $parser->parseTOMLStr($toml)->toArray();

        ////////////////////////////$this->assertSame($data['a']['b']['c']['json']['key1'], 1);
    ////////////////////////////}

    //////////////////////public function testCommentStripping() {
        //////////////////////$toml = <<<TOML
//////////////////////# comment 1
//////////////////////# comment 2
//////////////////////# comment 3
//////////////////////TOML;

        //////////////////////$parser = new TOMLParser();
        //////////////////////$this->assertSame($parser->parseTOMLStr($toml)->getRawContent(), "");
    //////////////////////}

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////public function testTrailingComma() {
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$toml = <<<TOML
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////[section]
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////keys = {
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////subkey1 = 'value1',
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////subkey2 = 'value2',
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////subkey3 = 'value3',
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////TOML;

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$parser = new TOMLParser();
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$data = $parser->parseTOMLStr($toml)->toArray();
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$this->assertSame($data['section']['keys']['subkey3'], 'value3');

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$toml = <<<TOML
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////[section]
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////keys = [
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////'value1',
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////'value2',
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////'value3',]
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////TOML;

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$parser = new TOMLParser();
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$data = $parser->parseTOMLStr($toml)->toArray();
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$this->assertSame($data['section']['keys'][1], 'value2');
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$this->assertSame($data['section']['keys'][2], 'value3');

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$toml = <<<TOML
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////[section]
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////keys = [
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////'value1',
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////'value2', # this is value2
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////'value3',
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////]
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////TOML;

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$parser = new TOMLParser();
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$data = $parser->parseTOMLStr($toml)->toArray();
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$this->assertSame($data['section']['keys'][1], 'value2');
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$this->assertSame($data['section']['keys'][2], 'value3');

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$toml = <<<TOML
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////[section]
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////keys = [
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////'value1',
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////'value2', # this is value2
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////'value3','value4',
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////'value5',
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////]
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////TOML;

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$parser = new TOMLParser();
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$data = $parser->parseTOMLStr($toml)->toArray();
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$this->assertSame($data['section']['keys'][3], 'value4');
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////$this->assertSame($data['section']['keys'][4], 'value5');
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////}

    ////////////////////////////public function testInlineTable() {
        ////////////////////////////$toml = <<<TOML
////////////////////////////[area]
////////////////////////////points = [ { x = 1, y = 2, z = 3 },
////////////////////////////{ x = 7, y = 8, z = 9 },
////////////////////////////{ x = 2, y = 4, z = 8 } ]
////////////////////////////TOML;
        ////////////////////////////$parser = new TOMLParser();
        ////////////////////////////$data = $parser->parseTOMLStr($toml)->toArray();
        ////////////////////////////$this->assertSame($data['area']['points'][0]['x'], 1);
        ////////////////////////////$this->assertSame($data['area']['points'][1]['y'], 8);
        ////////////////////////////$this->assertSame($data['area']['points'][2]['y'], 4);
    ////////////////////////////}

    //////////////////////////public function testFilterAddAndRemove() {
        //////////////////////////$toml = <<<TOML
//////////////////////////#comment
//////////////////////////[section1]
//////////////////////////TOML;
        //////////////////////////$parser = new TOMLParser();
        //////////////////////////$content = $parser
            //////////////////////////->removeFilter("remove_comments")
            //////////////////////////->parseTOMLStr($toml)
            //////////////////////////->getRawContent();
        //////////////////////////$this->assertTrue(strpos($content, "#") !== FALSE);
    //////////////////////////}

    //////////////////////////////////////////public function testNoSectionKeys() {
        //////////////////////////////////////////$toml = <<<TOML
//////////////////////////////////////////title = 'simpletitle'

//////////////////////////////////////////a = 1
//////////////////////////////////////////b = 2

//////////////////////////////////////////[c]
//////////////////////////////////////////d = 3
//////////////////////////////////////////e = { f = 4 }

//////////////////////////////////////////TOML;
        //////////////////////////////////////////$parser = new TOMLParser();
        //////////////////////////////////////////$data = $parser->parseTOMLStr($toml)->toArray();

        //////////////////////////////////////////$this->assertSame($data['title'], 'simpletitle');
        //////////////////////////////////////////$this->assertSame($data['a'], 1);
        //////////////////////////////////////////$this->assertSame($data['b'], 2);
        //////////////////////////////////////////$this->assertSame($data['c']['e']['f'], 4);
    //////////////////////////////////////////}

    //////////////////////////////////public function testSpecialChars() {
        //////////////////////////////////$toml = <<<TOML
//////////////////////////////////[abc]
//////////////////////////////////links = [
    //////////////////////////////////{ link = '/mywebsite?section=123', name = "a = name1", owner = 'abc@xyz.com' },
    //////////////////////////////////{ link = "/mywebsite?section=456", name = 'name2' },
    //////////////////////////////////{ link = "/mywebsite?section=789", name = "name3" },
//////////////////////////////////]
//////////////////////////////////TOML;
        //////////////////////////////////$parser = new TOMLParser();
        //////////////////////////////////$data = $parser->parseTOMLStr($toml)->toArray();
        //////////////////////////////////$this->assertSame($data['abc']['links'][1]['name'], 'name2');
        //////////////////////////////////$this->assertSame($data['abc']['links'][2]['name'], 'name3');
        //////////////////////////////////$this->assertSame($data['abc']['links'][0]['owner'], 'abc@xyz.com');
        //////////////////////////////////$this->assertSame($data['abc']['links'][0]['name'], 'a = name1');
    //////////////////////////////////}

    ////////////////////////////////////////public function testNestedJSON() {
        ////////////////////////////////////////$toml = <<<TOML
////////////////////////////////////////[abc]
////////////////////////////////////////json = {
    ////////////////////////////////////////keyset1 = {
        ////////////////////////////////////////subkey1 = "subval1",
        ////////////////////////////////////////subkey2 = "subval2"
    ////////////////////////////////////////},
    ////////////////////////////////////////keyset2 = {
        ////////////////////////////////////////subkey1 = "subval1",
        ////////////////////////////////////////subkey2 = "subval2"
    ////////////////////////////////////////}
////////////////////////////////////////}
////////////////////////////////////////TOML;
        ////////////////////////////////////////$parser = new TOMLParser();
        ////////////////////////////////////////$data = $parser->parseTOMLStr($toml)->toArray();
        ////////////////////////////////////////$this->assertSame($data['abc']['json']['keyset1']['subkey2'], 'subval2');
        ////////////////////////////////////////$this->assertSame($data['abc']['json']['keyset2']['subkey1'], 'subval1');
    ////////////////////////////////////////}

    ////////////////////////////////////////////////////////////public function testMultiNestedJSON() {
        ////////////////////////////////////////////////////////////$toml = <<<TOML
////////////////////////////////////////////////////////////[abc.efg]
////////////////////////////////////////////////////////////json1 = {
    ////////////////////////////////////////////////////////////keyset1 = {
        ////////////////////////////////////////////////////////////subkey1 = "subval1",
        ////////////////////////////////////////////////////////////subkey2 = "subval2"
    ////////////////////////////////////////////////////////////},
    ////////////////////////////////////////////////////////////keyset2 = {
        ////////////////////////////////////////////////////////////subkey1 = "subval1",
        ////////////////////////////////////////////////////////////subkey2 = "subval2"
    ////////////////////////////////////////////////////////////}
////////////////////////////////////////////////////////////}
////////////////////////////////////////////////////////////json2 = {
    ////////////////////////////////////////////////////////////keyset1 = {
        ////////////////////////////////////////////////////////////subkey1 = "subval11",
        ////////////////////////////////////////////////////////////subkey2 = "subval22"
    ////////////////////////////////////////////////////////////},
    ////////////////////////////////////////////////////////////keyset2 = {
        ////////////////////////////////////////////////////////////subkey1 = "subval11",
        ////////////////////////////////////////////////////////////subkey2 = "subval22"
    ////////////////////////////////////////////////////////////}
////////////////////////////////////////////////////////////}
////////////////////////////////////////////////////////////TOML;
        ////////////////////////////////////////////////////////////$parser = new TOMLParser();
        ////////////////////////////////////////////////////////////$data = $parser->parseTOMLStr($toml)->toArray();
        ////////////////////////////////////////////////////////////$this->assertSame($data['abc']['efg']['json1']['keyset1']['subkey2'], 'subval2');
        ////////////////////////////////////////////////////////////$this->assertSame($data['abc']['efg']['json2']['keyset2']['subkey2'], 'subval22');
    ////////////////////////////////////////////////////////////}

    ////////////////////////////public function testSimpleTOMLDoc() {
        ////////////////////////////$parser = new TOMLParser();
        ////////////////////////////$data = $parser->parseTOMLFile(__DIR__ . "/../../documents/doc1.toml")->toArray();
        ////////////////////////////$this->assertSame($data['html']['head']['title'], 'title');
        ////////////////////////////$this->assertSame($data['html']['head']['title2'], 'commontitle2');
        ////////////////////////////$this->assertSame($data['html']['head']['title3'], 'commontitle3');
        ////////////////////////////$this->assertSame($data['html']['body']['div']['id'], 'randomid');
        ////////////////////////////$this->assertSame($data['html']['body']['div']['ul']['class'], 'simplelist');
        ////////////////////////////$this->assertSame($data['html']['body']['div']['ul']['li']['style'], 'list-style-type:none');
        ////////////////////////////$this->assertSame($data['html']['body']['div']['ul']['li']['a'], '1');
        ////////////////////////////$this->assertSame($data['html']['body']['div']['ul']['li']['test'], '#12345');
        ////////////////////////////$this->assertSame($data['html']['body']['div']['style'], 'width:100px');
    ////////////////////////////}

    public function testBackSlashes() {
        $toml1 = <<<TOML
[a.b.c]
key = "\ABC\EFG"
key2 = "abc"
TOML;

        $toml2 = <<<TOML
[a.b.c]
key2 = "abc"
key = "\ABC\EFG"
TOML;

        $toml3 = <<<TOML
[a.b.c]
key2 = "abc"
url = "http://www.google.com"
name = "google"
key = "\ABC\EFG"
TOML;

        $toml4 = <<<TOML
[a.b.c]
# we can now define the actual class that is handling the acl-manager logic
key1 = "\ABCD\EFG"
key2 = "ok" # point acl manager to default datastore
key3 = "http://dummy.io"
key4 = "dummy@dummy.com"
password = "111111"
TOML;

        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml1)->toArray();
        $this->assertSame($data['a']['b']['c']['key'], "\ABC\EFG");

        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml2)->toArray();
        $this->assertSame($data['a']['b']['c']['key'], "\ABC\EFG");

        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml3)->toArray();
        $this->assertSame($data['a']['b']['c']['key2'], "abc");
        $this->assertSame($data['a']['b']['c']['url'], "http://www.google.com");
        $this->assertSame($data['a']['b']['c']['name'], "google");
        $this->assertSame($data['a']['b']['c']['key'], "\ABC\EFG");

        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml4)->toArray();

        $this->assertSame($data['a']['b']['c']['key2'], "ok");
        $this->assertSame($data['a']['b']['c']['key3'], "http://dummy.io");
    }

    public function testQuestionMark() {
        $toml1 = <<<TOML
[a.b.c]
key = "\ABC\EFG"
key2 = "abc"
url = "http://localhost?item_id<eq>1"
TOML;

        $parser = new TOMLParser();
        $data = $parser->parseTOMLStr($toml1)->toArray();
        $this->assertSame($data['a']['b']['c']['url'], "http://localhost?item_id<eq>1");
    }
}
