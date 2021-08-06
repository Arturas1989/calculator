<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'mark_name',
        'board_id',
    ];
    use HasFactory;

    public function product()
    {
        return $this->hasMany(Product::class);
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }
}