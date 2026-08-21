<?php
namespace NexaT\Models;

use NexaT\Core\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'id';
    protected $fillable = ['school_id', 'name', 'code'];
    protected $guarded = ['id'];
    protected $timestamps = true;
}