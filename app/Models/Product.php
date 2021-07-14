<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'code',
        'description',
        'from_sheet_count',
        'sheet_width',
        'sheet_length',
        'bending',
        'company_id',
        'mark_id',
        'board_id',
    ];
    use HasFactory;

    public function order()
    {
        return $this->hasMany(Order::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class,'company_id');
    }
    public function mark()
    {
        return $this->belongsTo(Mark::class,'mark_id');
    }
    public function board()
    {
        return $this->belongsTo(Board::class,'board_id');
    }
}
