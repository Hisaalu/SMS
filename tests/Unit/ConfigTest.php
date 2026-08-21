<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use NexaT\Core\Config;

class ConfigTest extends TestCase
{
    public function testCanGetConfigValue()
    {
        $value = Config::get('app.name');
        $this->assertEquals('NexaT School Management System', $value);
    }
    
    public function testCanGetNestedConfigValue()
    {
        $host = Config::get('database.host');
        $this->assertEquals('127.0.0.1', $host);
    }
    
    public function testGetReturnsDefaultWhenMissing()
    {
        $value = Config::get('non.existent.key', 'default');
        $this->assertEquals('default', $value);
    }
    
    public function testCanSetConfigValue()
    {
        Config::set('test.key', 'test_value');
        $value = Config::get('test.key');
        $this->assertEquals('test_value', $value);
    }
}