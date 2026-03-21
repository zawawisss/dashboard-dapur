<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyReport extends Model
{
    protected $table = 'monthly_reports_view';
    
    // Virtual ID derived from user_id + formatted date
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    public $timestamps = false;

    protected $casts = [
        'period' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
