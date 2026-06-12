<?php

namespace Modules\BlogNews\Http\Controllers\blog;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\BlogNews\Entities\BlogPost;
use Modules\BlogNews\Entities\NewsPost;
use Modules\BlogNews\Entities\BlogComment;
use Modules\BlogNews\Entities\NewsComment;
use Modules\BlogNews\Entities\BlogViewsReport;
use Modules\BlogNews\Entities\NewsViewsReport;

class BlogDashboardController extends Controller
{
    public function dashboard()
    {
        $data = [];
        try {
            $data['title'] = __("Blog and News Dashboard");
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
            $posts = BlogPost::get();
            $reports = BlogViewsReport::orderBy('id', 'asc')->limit(7)->get();
            foreach($days as $day => $count) {
                $chart_title .= '"'.$day.'", ';
                $report = $reports->where('day', $day)->first();
                if($report)
                    $days[$day] = $report->count;
                    $chart_value .= '"'. $days[$day] .'", ';
            }
            $data['blogs'] = $posts->count();
            $data['comments'] = BlogComment::get()->count();
            $data['published'] = $posts->where('publish', STATUS_ACTIVE)->count();
            $data['active'] = $posts->where('status', STATUS_ACTIVE)->count();
            $data['chart_title'] = '['.$chart_title.']';
            $data['chart_value'] = '['.$chart_value.']';

            $news_day = [
                'Sunday' => 0,
                'Monday' => 0,
                'Tuesday' => 0,
                'Wednesday' => 0,
                'Thursday' => 0,
                'Friday' => 0,
                'Saturday' => 0
            ];
            $news_chart_title = '';
            $news_chart_value = '';
            $news = NewsPost::get();
            $newsReports = NewsViewsReport::orderBy('id', 'asc')->limit(7)->get();
            foreach($news_day as $day => $count) {
                $news_chart_title .= '"'.$day.'", ';
                $report = $newsReports->where('day', $day)->first();
                if($report)
                    $news_day[$day] = $report->count;
                    $news_chart_value .= '"'. $news_day[$day] .'", ';
            }
            $data['news_news'] = $news->count();
            $data['news_comments'] = NewsComment::get()->count();
            $data['news_published'] = $news->where('publish', STATUS_ACTIVE)->count();
            $data['news_active'] = $news->where('status', STATUS_ACTIVE)->count();
            $data['news_chart_title'] = '['.$news_chart_title.']';
            $data['news_chart_value'] = '['.$news_chart_value.']';






        } catch (\Exception $e) {
            storeException('BlogDashboard',$e->getMessage());
        }
        return view('blognews::blog.dashboard',$data);
    }
    // public function dashboard()
    // {
    //     $data = [];
    //     try {
    //         $chart_title = '';
    //         $chart_value = '';
    //         $days = [
    //             'Sunday' => 0,
    //             'Monday' => 0,
    //             'Tuesday' => 0,
    //             'Wednesday' => 0,
    //             'Thursday' => 0,
    //             'Friday' => 0,
    //             'Saturday' => 0
    //         ];
    //         $posts = BlogPost::get();
    //         $reports = BlogViewsReport::orderBy('id', 'asc')->limit(7)->get();
    //         foreach($days as $day => $count) {
    //             $chart_title .= '"'.$day.'", ';
    //             $report = $reports->where('day', $day)->first();
    //             if($report)
    //                 $days[$day] = $report->count;
    //                 $chart_value .= '"'. $days[$day] .'", ';
    //         }
    //         $data['blogs'] = $posts->count();
    //         $data['comments'] = BlogComment::get()->count();
    //         $data['published'] = $posts->where('publish', STATUS_ACTIVE)->count();
    //         $data['active'] = $posts->where('status', STATUS_ACTIVE)->count();
    //         $data['chart_title'] = '['.$chart_title.']';
    //         $data['chart_value'] = '['.$chart_value.']';
    //     } catch (\Exception $e) {
    //         storeException('BlogDashboard',$e->getMessage());
    //     }
    //     return view('blognews::blog.dashboard',$data);
    // }

}
