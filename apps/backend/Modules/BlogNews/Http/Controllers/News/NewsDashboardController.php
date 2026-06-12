<?php

namespace Modules\BlogNews\Http\Controllers\News;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BlogNews\Entities\NewsPost;
use Modules\BlogNews\Entities\NewsComment;
use Modules\BlogNews\Entities\NewsViewsReport;

class NewsDashboardController extends Controller
{
    public function dashboard()
    {
        $data = [];
        try {
            $chart_title = '';
            $chart_value = '';
            $days = [
                'Sunday' => 0,
                'Monday' => 0,
                'Tuesday' => 0,
                'Wednesday' => 0,
                'Thursday' => 0,
                'Friday' => 0,
                'Saturday' => 0
            ];
            $posts = NewsPost::get();
            $reports = NewsViewsReport::orderBy('id', 'asc')->limit(7)->get();
            foreach($days as $day => $count) {
                $chart_title .= '"'.$day.'", ';
                $report = $reports->where('day', $day)->first();
                if($report)
                    $days[$day] = $report->count;
                    $chart_value .= '"'. $days[$day] .'", ';
            }
            $data['news'] = $posts->count();
            $data['comments'] = NewsComment::get()->count();
            $data['published'] = $posts->where('publish', STATUS_ACTIVE)->count();
            $data['active'] = $posts->where('status', STATUS_ACTIVE)->count();
            $data['chart_title'] = '['.$chart_title.']';
            $data['chart_value'] = '['.$chart_value.']';
        } catch (\Exception $e) {
            storeException('NewsDashboard',$e->getMessage());
        }
        return view('blognews::news.dashboard',$data);
    }
}
