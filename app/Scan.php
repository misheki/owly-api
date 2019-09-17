<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'worker_code', 'scan_dt', 'edited_by', 'edit_remarks', 'edited_at'
    ];

    protected $dates = ['scandt', 'edited_at'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
