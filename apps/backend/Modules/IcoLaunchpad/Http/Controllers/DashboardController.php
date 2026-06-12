<?php

namespace Modules\IcoLaunchpad\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\IcoLaunchpad\Entities\IcoToken;
use Modules\IcoLaunchpad\Entities\TokenBuyEarn;
use Modules\IcoLaunchpad\Entities\SubmitFormLists;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function dashboardICO()
    {

        $allMonths = all_months();
        // Total Sale
        $monthlySale = TokenBuyHistory::select(DB::raw('sum(amount) as total'), DB::raw('MONTH(created_at) as months'))
            ->whereYear('created_at', '2022')
            ->where('status', STATUS_ACCEPTED)
            ->groupBy('months')
            ->get();
        
        if (isset($monthlySale[0])) {
            foreach ($monthlySale as $sale) {
                $data['sale'][$sale->months] = $sale->total;
            }
        }
        $allSale = [];
        foreach ($allMonths as $month) {
            $allSale[] =  isset($data['sale'][$month]) ? $data['sale'][$month] : 0;
        }
        $data['monthly_sale'] = $allSale; 


        $data['title'] = __('ICO Dashboard');
        $data['recent_tokens'] = IcoToken::where(['approved_status' => STATUS_ACCEPTED])->with('user')->latest()->limit(9)->get();
        $data['launchpad_request'] = SubmitFormLists::get()->count();
        $data['total_earn'] = TokenBuyEarn::sum('earn');
        // $data[''] = 
        return view('icolaunchpad::dashboard',$data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('icolaunchpad::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('icolaunchpad::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('icolaunchpad::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
