<?php
// File: /app/Models/Student.php

namespace NexaT\Models;

use NexaT\Core\Model;

class Student extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 'registration_number', 'admission_number',
        'first_name', 'middle_name', 'last_name', 'preferred_name',
        'gender', 'date_of_birth', 'photo_path', 'phone', 'email', 'address',
        'current_category_id', 'current_status_id', 'admission_date',
    ];
    protected $guarded = ['id'];
    protected $timestamps = true;
}