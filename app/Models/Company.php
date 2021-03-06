<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'company_name',
    ];
    use HasFactory;

    public function product()
    {
        return $this->hasMany(Product::class);
    }
}
