<?php

require_once __DIR__ . '/bootstrap-simple.php';

class BasicWorkingTest extends PHPUnit\Framework\TestCase
{
    public function testBasic()
    {
        $this->assertTrue(true);
    }
} 