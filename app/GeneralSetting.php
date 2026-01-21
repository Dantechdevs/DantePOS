<?php

namespace App;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    use LogsActivity;
    protected $fillable = [
        'id',
        'company_name',
        'company_email',
        'company_mobile',
        'company_address',
        'company_logo',
        'updatedBy'
    ];
    protected $casts = [
        'company_mobile' => 'array',
    ];
}
