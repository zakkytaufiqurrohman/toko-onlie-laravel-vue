<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookOrder extends Model
{
    protected $fillable = [
        'book_id', 'order_id', 'quantity'
    ];
}
