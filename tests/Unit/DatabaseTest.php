<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use NexaT\Core\Database;
use PDO;

class DatabaseTest extends TestCase
{
    private $db;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::getInstance();
    }
    
    public function testDatabaseConnectionIsEstablished()
    {
        $connection = $this->db->getConnection();
        $this->assertInstanceOf(PDO::class, $connection);
    }
    
    public function testCanExecuteQuery()
    {
        $result = $this->db->execute("SELECT 1 AS test");
        $this->assertTrue($result);
    }
    
    public function testCanFetchSingleRow()
    {
        $result = $this->db->fetch("SELECT 1 AS test");
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['test']);
    }
    
    public function testCanInsertAndRetrieveData()
    {
        $this->db->execute("CREATE TABLE IF NOT EXISTS test_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100)
        )");
        
        $id = $this->db->insert('test_table', ['name' => 'Test Name']);
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
        
        $result = $this->db->fetch("SELECT * FROM test_table WHERE id = ?", [$id]);
        $this->assertEquals('Test Name', $result['name']);
        
        $this->db->execute("DROP TABLE test_table");
    }
    
    public function testTransactionWorks()
    {
        $this->db->beginTransaction();
        
        $this->db->execute("CREATE TABLE IF NOT EXISTS test_transaction (
            id INT AUTO_INCREMENT PRIMARY KEY,
            value VARCHAR(100)
        )");
        
        $this->db->insert('test_transaction', ['value' => 'test']);
        $this->db->rollback();
        
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM test_transaction");
        $this->assertEquals(0, $result['count']);
        
        $this->db->execute("DROP TABLE test_transaction");
    }
}