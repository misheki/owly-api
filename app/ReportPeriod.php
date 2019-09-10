<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportPeriod extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id', 'title', 'run_on', 'period_start_date', 'period_start_month', 'period_end_date', 'period_end_month'
    ];

    public function organization()
    {
        return $this->belongsTo('App\Organization');
    }
}
