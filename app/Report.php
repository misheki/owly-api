<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id', 'title', 'report_period', 'type', 'filename', 'file_path'
    ];

    public function organization()
    {
        return $this->belongsTo('App\Organization');
    }
}
