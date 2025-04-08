<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AdminHierarchies extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    /**
    * The table associated with the model.
    *
    * @var string
    */
    protected $table = 'admin_hierarchies';

    /**
    * The primary key associated with the table.
    *
    * @var string
    */
    protected $primaryKey = 'admin_hierarchy_id';

    /**
    * Indicates if the model's ID is not auto-incrementing.
    *
    * @var bool
    */
    public $incrementing = false;

    /**
    * The data type of the auto-incrementing ID.
    *
    * @var string
    */
    protected $keyType = 'string';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'admin_hierarchy_id',
        'uuid',
        'admin_hierarchy_name',
        'created_by',
        'parent_id'
    ];

    /**
    * The attributes that should be mutated to dates.
    *
    * @var array
    */
    protected $dates = ['deleted_at'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }
}
