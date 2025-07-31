<?php

require_once __DIR__ . '/bootstrap-simple.php';

class CupompromoSimpleTest extends PHPUnit\Framework\TestCase
{
    public function testBasic()
    {
        $this->assertTrue(true);
    }
    
    public function testConstants()
    {
        $this->assertTrue(defined('CUPOMPROMO_PLUGIN_PATH'));
        $this->assertTrue(defined('CUPOMPROMO_VERSION'));
    }
    
    public function testHelperFunctions()
    {
        $this->assertTrue(function_exists('cupompromo_mock_awin_data'));
        
        $data = cupompromo_mock_awin_data();
        $this->assertIsArray($data);
        $this->assertEquals(12345, $data['id']);
    }
    
    public function testBasicMath()
    {
        $this->assertEquals(4, 2 + 2);
        $this->assertEquals(0, 2 - 2);
        $this->assertEquals(4, 2 * 2);
        $this->assertEquals(1, 2 / 2);
    }
    
    public function testStrings()
    {
        $string = 'Cupompromo';
        $this->assertEquals('Cupompromo', $string);
        $this->assertEquals(10, strlen($string));
        $this->assertStringContainsString('promo', $string);
    }
    
    public function testArrays()
    {
        $array = array('cupom', 'promo', 'desconto');
        $this->assertIsArray($array);
        $this->assertCount(3, $array);
        $this->assertContains('cupom', $array);
    }
} 