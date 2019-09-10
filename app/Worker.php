<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worker extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id', 'worker_code', 'name', 'code', 'address', 'phone', 'started_at', 'ended_at', 'staff_id', 'qr', 'status', 'qr', 'status'
    ];

    public function organization()
    {
        return $this->belongsTo('App\Organization');
    }
}
