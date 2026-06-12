<?php

namespace App\Http\Controllers;

use App\Enums\DepositeStatus;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        return deposit_status();
        // return DepositeStatus::statusListHtml();
    }
}
