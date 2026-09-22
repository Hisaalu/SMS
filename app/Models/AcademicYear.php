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

    public function terms(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM terms WHERE academic_year_id = ? ORDER BY term_number ASC",
            [$this->id]
        );
    }

    public function getCurrentTerm(): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM terms WHERE academic_year_id = ? AND is_current = 1 LIMIT 1",
            [$this->id]
        ) ?: null;
    }

    public static function getCurrent(?int $schoolId = null): ?array
    {
        $instance = new static();
        $schoolId = $schoolId ?? $instance->schoolId ?? null;

        if (!$schoolId) {
            return null;
        }

        return $instance->db->fetch(
            "SELECT * FROM academic_years WHERE school_id = ? AND is_current = 1 LIMIT 1",
            [$schoolId]
        ) ?: null;
    }

    public function setCurrent(): bool
    {
        if (!empty($this->school_id)) {
            $this->db->update('academic_years', ['is_current' => 0], ['school_id' => $this->school_id]);
        } else {
            $this->db->execute("UPDATE academic_years SET is_current = 0");
        }

        $this->is_current = 1;

        return $this->save();
    }
}