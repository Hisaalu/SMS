<?php
// File: /app/Models/SchoolClass.php

namespace NexaT\Models;

use NexaT\Core\Model;

class SchoolClass extends Model
{
    protected $table = 'classes';
    protected $primaryKey = 'id';
    protected $fillable = ['school_id', 'name', 'code'];
    protected $guarded = ['id'];
    protected $timestamps = true;

    public function streams()
    {
        return Stream::where('class_id', $this->id);
    }
}