<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;

    public function product()
    {
        return $this->hasMany(Product::class);
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function getAllRelationsByIdAndDate($request, $from, $to, $from2,$to2)
    {
        if($from2 && $to2)
        {
            return $this->whereIn('id',$request->boards)->with(['marks.product'=> function($q)
            {
                return $q->orderBy('sheet_width','desc');
            }
            , 'marks.product.order'=> function($q) use ($from, $to, $from2, $to2)
            {
                return $q->whereBetween('manufactury_date', [$from, $to])
                ->whereBetween('load_date', [$from2, $to2]);
            }, 'marks.product.company'])->get();
        }
        else if ($from && $to) 
        {
            return $this->whereIn('id',$request->boards)->with(['marks.product'=> function($q)
            {
                return $q->orderBy('sheet_width','desc');
            }
            , 'marks.product.order'=> function($q) use ($from, $to)
            {
                return $q->whereBetween('manufactury_date', [$from, $to]);
            }, 'marks.product.company'])->get();
        }
        // if($from2 && $to2)
        // {
        //     return $this->whereIn('id',$request->boards)->with(['marks.product.order'
        //     => function($q) use ($from, $to, $from2, $to2){
        //             return $q->whereBetween('manufactury_date', [$from, $to])
        //             ->whereBetween('load_date', [$from2, $to2]);
        //         }, 'marks.product.company'])->get();
        // }
        // else if ($from && $to) 
        // {
        //     return $this->whereIn('id',$request->boards)->with(['marks.product.order'
        //     => function($q) use ($from, $to){
        //             return $q->whereBetween('manufactury_date', [$from, $to]);
        //         }, 'marks.product.company'])->get();
        // }
        return [];
    }
}
