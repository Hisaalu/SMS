<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use NexaT\Core\SettingsService;
use NexaT\Core\Database;

class SettingsServiceTest extends TestCase
{
    private $settings;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->settings = new SettingsService();
        
        $db = Database::getInstance();
        $db->execute("DELETE FROM settings WHERE setting_key LIKE 'test_%'");
    }
    
    protected function tearDown(): void
    {
        $db = Database::getInstance();
        $db->execute("DELETE FROM settings WHERE setting_key LIKE 'test_%'");
        parent::tearDown();
    }
    
    public function testCanSetAndGetStringSetting()
    {
        $this->settings->set('test_string', 'Hello World', 'string');
        $value = $this->settings->get('test_string');
        
        $this->assertEquals('Hello World', $value);
    }
    
    public function testCanSetAndGetIntegerSetting()
    {
        $this->settings->set('test_integer', 42, 'integer');
        $value = $this->settings->get('test_integer');
        
        $this->assertEquals(42, $value);
        $this->assertIsInt($value);
    }
    
    public function testCanSetAndGetBooleanSetting()
    {
        $this->settings->set('test_boolean_true', true, 'boolean');
        $this->settings->set('test_boolean_false', false, 'boolean');
        
        $this->assertTrue($this->settings->get('test_boolean_true'));
        $this->assertFalse($this->settings->get('test_boolean_false'));
    }
    
    public function testCanSetAndGetJsonSetting()
    {
        $data = ['key1' => 'value1', 'key2' => ['nested' => 'value']];
        $this->settings->set('test_json', $data, 'json');
        $value = $this->settings->get('test_json');
        
        $this->assertEquals($data, $value);
        $this->assertIsArray($value);
    }
    
    public function testGetReturnsDefaultWhenSettingNotFound()
    {
        $value = $this->settings->get('non_existent_key', 'default_value');
        $this->assertEquals('default_value', $value);
    }
    
    public function testThemeSettingsReturnCorrectStructure()
    {
        $theme = $this->settings->getTheme();
        
        $this->assertIsArray($theme);
        $this->assertArrayHasKey('primary', $theme);
        $this->assertArrayHasKey('secondary', $theme);
        $this->assertArrayHasKey('accent', $theme);
        $this->assertArrayHasKey('background', $theme);
        $this->assertArrayHasKey('surface', $theme);
        $this->assertArrayHasKey('text', $theme);
        $this->assertArrayHasKey('muted', $theme);
        $this->assertArrayHasKey('border', $theme);
        $this->assertArrayHasKey('success', $theme);
        $this->assertArrayHasKey('warning', $theme);
        $this->assertArrayHasKey('danger', $theme);
        $this->assertArrayHasKey('dark_mode', $theme);
    }
    
    public function testSchoolProfileReturnsCorrectStructure()
    {
        $profile = $this->settings->getSchoolProfile();
        
        $this->assertIsArray($profile);
        $this->assertArrayHasKey('name', $profile);
        $this->assertArrayHasKey('short_name', $profile);
        $this->assertArrayHasKey('motto', $profile);
        $this->assertArrayHasKey('telephone', $profile);
        $this->assertArrayHasKey('email', $profile);
    }
}