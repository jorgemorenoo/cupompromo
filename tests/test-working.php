<?php

require_once __DIR__ . '/bootstrap-simple.php';

class WorkingTest extends PHPUnit\Framework\TestCase
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
} 