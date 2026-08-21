<?php

namespace NexaT\Models;

use NexaT\Core\Model;

class StudentStatus extends Model
{
    protected $table = 'student_statuses';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'is_active_status',
        'allows_enrollment',
        'status',
        'display_order'
    ];
}