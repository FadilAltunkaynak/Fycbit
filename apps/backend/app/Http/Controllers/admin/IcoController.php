<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IcoController extends Controller
{
    public function icoList()
    {
        $data['title'] = __('List of ICO');
        return view('admin.ico.ico-list',$data);
    }
}
