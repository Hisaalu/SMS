<?php
// File: /app/Models/Term.php

namespace NexaT\Models;

use NexaT\Core\Model;

class Term extends Model
{
    protected $table = 'terms';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 'academic_year_id', 'name', 'term_number',
        'start_date', 'end_date', 'is_current', 'status',
    ];
    protected $guarded = ['id'];
    protected $timestamps = true;

    public function academicYear(): ?AcademicYear
    {
        return AcademicYear::find($this->academic_year_id);
    }

    public function setCurrent(): bool
    {
        $this->db->update('terms', ['is_current' => 0], [
            'academic_year_id' => $this->academic_year_id,
        ]);

        $this->is_current = 1;

        return $this->save();
    }

    public static function getCurrent(): ?array
    {
        $instance = new static();

        $row = $instance->db->fetch(
            "SELECT * FROM terms WHERE school_id = ? AND is_current = 1 LIMIT 1",
            [$instance->schoolId]
        );

        return $row ?: null;
    }
}