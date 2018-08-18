<?php

use DaveismynameLaravel\MsGraph\Facades\MsGraph;

Route::group(['middleware' => ['web', 'auth']], function(){
    Route::get('msgraph', function(){

        if (!is_string(MsGraph::getAccessToken())) {
            return redirect('msgraph/oauth');
        } else {

            return MsGraph::contacts();
        }
    });

    Route::get('msgraph/oauth', function(){
        return MsGraph::connect();
    });
});
