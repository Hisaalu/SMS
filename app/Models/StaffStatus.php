<?php
// File: /app/Models/StaffStatus.php

namespace NexaT\Models;

use NexaT\Core\Model;

class StaffStatus extends Model
{
    protected $table = 'staff_statuses';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 'name', 'code', 'description',
        'is_active_status', 'allows_login', 'status', 'display_order',
    ];
    protected $guarded = ['id'];
    protected $timestamps = true;
}