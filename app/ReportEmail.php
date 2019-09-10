<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportEmail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id', 'email_address', 'name'
    ];

    public function organization()
    {
        return $this->belongsTo('App\Organization');
    }
}
