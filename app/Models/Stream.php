<?php
// File: /app/Models/Stream.php

namespace NexaT\Models;

use NexaT\Core\Model;

class Stream extends Model
{
    protected $table = 'streams';
    protected $primaryKey = 'id';
    protected $fillable = ['school_id', 'class_id', 'name', 'capacity', 'status'];
    protected $guarded = ['id'];
    protected $timestamps = true;
}