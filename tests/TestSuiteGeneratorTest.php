<?php
require_once 'bootstrap.php';

class TestSuiteGeneratorTest extends PHPUnit_Framework_TestCase
{
    private $generator;

    function setUp()
    {
        $this->generator = new Ilib_Testing_Selenium_Selenese_TestSuiteGenerator('./example');
    }

    function testConstruction()
    {
        $this->assertTrue(is_object($this->generator));
    }

    function testGenerate()
    {
        $this->assertEquals(file_get_contents('expected.html'), $this->generator->generate());
    }

    function testAddReplacementActuallyAddsReplacements()
    {
        $this->generator->addReplacement('test', 'test');
        $this->assertTrue(1 == count($this->generator->getReplacements()));
    }
}
