<?php

namespace Dcblogdev\MsGraph\Models;

use Illuminate\Database\Eloquent\Model;

class MsGraphToken extends Model
{
    protected $guarded = [];

    public function __construct()
    {
        parent::__construct();

        $this->setConnection(config('msgraph.dbConnection'));
    }
}
