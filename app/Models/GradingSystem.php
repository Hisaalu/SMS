<?php
// File: /app/Models/GradingSystem.php

namespace NexaT\Models;

use NexaT\Core\Model;

class GradingSystem extends Model
{
    protected $table = 'grading_systems';
    protected $primaryKey = 'id';
    protected $fillable = ['school_id', 'name', 'description', 'status', 'is_default'];
    protected $guarded = ['id'];
    protected $timestamps = true;
    
    public function rules()
    {
        $db = $this->db;
        return $db->fetchAll(
            "SELECT * FROM grading_rules WHERE system_id = :system_id ORDER BY min_mark DESC",
            ['system_id' => $this->id]
        );
    }
    
    public function getGrade($mark)
    {
        $rules = $this->rules();
        foreach ($rules as $rule) {
            if ($mark >= $rule['min_mark'] && $mark <= $rule['max_mark']) {
                return $rule;
            }
        }
        return null;
    }
}