<?php
// File: /app/Models/GradingSystem.php

namespace NexaT\Models;

use NexaT\Core\Model;

class GradingSystem extends Model
{
    protected $table = 'grading_systems';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 'class_id', 'academic_year_id',
        'name', 'description', 'is_default', 'status',
    ];
    protected $guarded = ['id'];
    protected $timestamps = true;

    public function rules(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM grading_rules WHERE system_id = :system_id ORDER BY min_mark DESC",
            ['system_id' => $this->id]
        );
    }

    public function getGrade(float $mark): ?array
    {
        foreach ($this->rules() as $rule) {
            if ($mark >= (float)$rule['min_mark'] && $mark <= (float)$rule['max_mark']) {
                return $rule;
            }
        }
        return null;
    }
}