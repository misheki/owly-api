<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'org_category_id', 'name', 'code', 'contact_person', 'email', 'address', 'website'
    ];

    public function org_category()
    {
        return $this->belongsTo('App\OrgCategory');
    }

    public function reports()
    {
        return $this->hasMany('App\Report');
    }

    public function report_emails()
    {
        return $this->hasMany('App\ReportEmail');
    }

    public function report_periods()
    {
        return $this->hasMany('App\ReportPeriod');
    }

    public function users()
    {
        return $this->hasMany('App\User');
    }

    public function workers()
    {
        return $this->hasMany('App\Worker');
    }

    public function scans()
    {
        return $this->hasManyThrough('App\Scan', 'App\User');
    }
}
