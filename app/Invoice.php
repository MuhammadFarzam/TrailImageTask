<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Invoice extends Eloquent
{
    protected $connection = 'mongodb';
	protected $collection = 'invoices';

    protected $fillable = [
        'invoice_no',
        'user_name',
        'subtotal',
        'discount',
        'shipping',
        'tax',
        'total_amount'
    ];

}
