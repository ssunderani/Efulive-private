<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dispatchout extends Model
{
    protected $guarded = [];

    protected $with = [
        'category:id,category_name',
        'subcategory:id,sub_cat_name',
        'inventory:id,product_sn,purchase_date,item_price,dollar_rate,make_id,model_id,devicetype_id,location_id,issued_to'
    ];

    public function category()
    {
        return $this->belongsTo('App\Category');
    }
    public function subcategory()
    {
        return $this->belongsTo('App\Subcategory');
    }
    public function inventory()
    {
        return $this->belongsTo('App\Inventory');
    }
}
