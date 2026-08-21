<?php
// File: /app/Models/AcademicYear.php

namespace NexaT\Models;

use NexaT\Core\Model;

class AcademicYear extends Model
{
    protected $table = 'academic_years';
    protected $primaryKey = 'id';
    protected $fillable = ['school_id', 'name', 'start_date', 'end_date', 'is_current', 'status'];
    protected $guarded = ['id'];
    protected $timestamps = true;

    public function terms()
    {
        $db = $this->db;
        return $db->fetchAll(
            "SELECT * FROM terms WHERE academic_year_id = ? ORDER BY term_number ASC",
            [$this->id]
        );
    }

    public function getCurrentTerm()
    {
        $db = $this->db;
        return $db->fetch(
            "SELECT * FROM terms WHERE academic_year_id = ? AND is_current = 1",
            [$this->id]
        );
    }

    public static function getCurrent($schoolId = null)
    {
        $instance = new static();
        $schoolId = $schoolId ?? ($instance->schoolId ?? null);
        
        return $instance->db->fetch(
            "SELECT * FROM academic_years WHERE school_id = ? AND is_current = 1 LIMIT 1",
            [$schoolId]
        );
    }

    public function setCurrent()
    {
        $db = $this->db;
        
        // Reset current flag only for this specific school
        if (!empty($this->school_id)) {
            $db->update('academic_years', ['is_current' => 0], ['school_id' => $this->school_id]);
        } else {
            $db->query("UPDATE academic_years SET is_current = 0");
        }

        // Set this record as current
        $this->is_current = 1;
        return $this->save();
    }
}