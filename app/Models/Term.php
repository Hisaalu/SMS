<?php
// File: /app/Models/Term.php

namespace NexaT\Models;

use NexaT\Core\Model;

class Term extends Model
{
    protected $table = 'terms';
    protected $primaryKey = 'id';
    protected $fillable = ['school_id', 'academic_year_id', 'name', 'term_number', 'start_date', 'end_date', 'is_current', 'status'];
    protected $guarded = ['id'];
    protected $timestamps = true;

    public function academicYear()
    {
        return AcademicYear::find($this->academic_year_id);
    }

    public function setCurrent()
    {
        $db = $this->db;
        // Reset all current flags for this academic year
        $db->update('terms', ['is_current' => 0], ['academic_year_id' => $this->academic_year_id]);
        // Set this one as current
        $this->is_current = 1;
        return $this->save();
    }

    public static function getCurrent()
    {
        $instance = new static();
        return $instance->db->fetch(
            "SELECT * FROM terms WHERE school_id = ? AND is_current = 1",
            [$instance->schoolId]
        );
    }
}