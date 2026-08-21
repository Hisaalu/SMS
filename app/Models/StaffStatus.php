<?php

namespace NexaT\Models;

use NexaT\Core\Model;

class StaffStatus extends Model
{
    protected $table = 'staff_statuses';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'is_active_status',
        'allows_login',
        'status',
        'display_order'
    ];
}