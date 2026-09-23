<?php
// File: /app/Models/Subject.php

namespace NexaT\Models;

use NexaT\Core\Model;

class Subject extends Model
{
    protected $table = 'subjects';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 'department_id', 'name', 'code',
        'type', 'description', 'status',
    ];
    protected $guarded = ['id'];
    protected $timestamps = true;

    public const NON_GRADED_TYPES = ['other'];

    public static function isGradedType(?string $type): bool
    {
        return !in_array(strtolower((string)$type), self::NON_GRADED_TYPES, true);
    }

    public function isGraded(): bool
    {
        return self::isGradedType($this->type);
    }
}