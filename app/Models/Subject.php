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
}