<?php

//use App\Http\Requests\Request;

//if (! function_exists('env')) {
    function set_active($path, $active = 'active')
    {
        return Request::is($path) || Request::is($path . '/*') ? $active: '';
    }
//}