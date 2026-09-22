<?php
// File: /app/Models/TeacherAssignment.php

namespace NexaT\Models;

use NexaT\Core\Model;

class TeacherAssignment extends Model
{
    protected $table = 'teacher_assignments';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 'staff_id', 'assignment_type_id',
        'academic_year_id', 'academic_period_id', 'department_id',
        'class_id', 'stream_id', 'subject_id',
        'start_date', 'end_date', 'status',
    ];
    protected $guarded = ['id'];
    protected $timestamps = true;
}