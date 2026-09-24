<?php
// File: /app/Models/Subject.php

namespace NexaT\Models;

use NexaT\Core\Model;

class Subject extends Model
{
    protected $table = 'subjects';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 'grading_system_id', 'grading_subject_type_id',
        'department_id', 'name', 'code', 'description', 'status',
    ];
    protected $guarded = ['id'];
    protected $timestamps = true;

    public const NON_GRADED_TYPES = ['other'];

    public function gradingSystem(): ?array
    {
        if (empty($this->grading_system_id)) {
            return null;
        }

        return $this->db->fetch(
            "SELECT * FROM grading_systems WHERE id = :id",
            ['id' => $this->grading_system_id]
        ) ?: null;
    }

    public function subjectType(): ?array
    {
        if (empty($this->grading_subject_type_id)) {
            return null;
        }

        return $this->db->fetch(
            "SELECT * FROM grading_subject_types WHERE id = :id",
            ['id' => $this->grading_subject_type_id]
        ) ?: null;
    }

    public function isGraded(): bool
    {
        $type = $this->subjectType();

        if ($type !== null) {
            return (bool)$type['is_graded'];
        }

        return !in_array(strtolower((string)($this->type ?? 'core')), self::NON_GRADED_TYPES, true);
    }

    public static function isGradedType(?string $type): bool
    {
        return !in_array(strtolower((string)$type), self::NON_GRADED_TYPES, true);
    }
}