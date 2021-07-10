<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'quantity',
        'load_date',
        'state_id',
    ];

    public function errorsHTML($field, $errors)
    {
        if(!$errors->has($field)){
            return '';
        }
        $html = '<ul class="list-group">';
        foreach ($errors->get($field) as $error) {
            $html.='<li class="list-group-item list-group-item-danger">' . $error .'</li>';
        }
        $html.= '</ul>';
        return
        '<div class="container"> 
            <div class="row justify-content-center">
                <div class="alert">'
                    .$html.
                '</div>
            </div>
        </div>'
            ;
    }
}
