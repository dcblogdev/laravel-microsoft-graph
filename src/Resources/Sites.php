<?php

namespace Dcblogdev\MsGraph\Resources;

use Dcblogdev\MsGraph\Facades\MsGraph;

class Sites extends MsGraph
{
    public function get(): array
    {
        return MsGraph::get('sites?search=*');
    }
}
