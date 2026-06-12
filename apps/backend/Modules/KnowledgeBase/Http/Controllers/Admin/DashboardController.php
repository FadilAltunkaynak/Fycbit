<?php

namespace Modules\KnowledgeBase\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\KnowledgeBase\Entities\KnbArticle;
use Modules\KnowledgeBase\Entities\KnbCategory;
use Modules\KnowledgeBase\Entities\KnbSubCategory;
use Modules\KnowledgeBase\Entities\SupportTicket;
use Modules\KnowledgeBase\Http\Services\SettingService;
use Modules\KnowledgeBase\Http\Services\Support\TicketService;
use Illuminate\Support\Facades\Validator;
use Modules\KnowledgeBase\Http\Requests\IconRequest;

class DashboardController extends Controller
{
    private $settingService;
    private $ticketService;
    public function __construct()
    {
        $this->settingService = new SettingService;
        $this->ticketService = new TicketService;
    }
    public function index()
    {
        $data['title'] = __('Knowledge Base and Support Dashboard');
        $data['total_category_of_article'] = KnbCategory::get()->count();
        $data['total_subcategory_of_article'] = KnbSubCategory::get()->count();
        $data['total_article'] = KnbArticle::get()->count();
        $data['total_unseen_ticket'] = SupportTicket::where('is_seen_by_user',SEEN)->get()->count();
        $data['total_seen_ticket'] = SupportTicket::where('is_seen_by_user',UNSEEN)->get()->count();
        $data['total_unassigned_ticket'] = SupportTicket::where('assigned_agent_id',null)->get()->count();

        $ticket_count_response = $this->ticketService->getUserTicketCountDetails();
        if($ticket_count_response['success'])
        {
            $ticket_count = $ticket_count_response['data'];
            $data['ticket_status'] = [$ticket_count['total_open_ticket_count'],$ticket_count['total_pending_ticket_count'],$ticket_count['total_close_ticket_count'],$ticket_count['total_close_forever_ticket_count']];
        }else{
            $data['ticket_status'] = [0,0,0,0];
        }
        

        $allMonths = all_months();
        // deposit
        $monthlyTickets = SupportTicket::select(DB::raw('count(id) as totalTicket'), DB::raw('MONTH(created_at) as months'))
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('months')
            ->get();

        if (isset($monthlyTickets[0])) {
            foreach ($monthlyTickets as $ticket) {
                $data['ticket'][$ticket->months] = $ticket->totalTicket;
            }
        }
        $allTickets = [];
        foreach ($allMonths as $month) {
            $allTickets[] =  isset($data['ticket'][$month]) ? $data['ticket'][$month] : 0;
        }
        $data['monthly_ticket'] = $allTickets;
        return view('knowledgebase::admin.dashboard',$data);
    }

    public function siteSettings()
    {
        $data['title'] = __('Knowledge Base and Support Site Settings');
        return view('knowledgebase::admin.site-settings.settings',$data);
    }

    public function siteSettingsUpdate(Request $request)
    {
        if(!empty($request->logo)){
            $rules['logo']='image|mimes:jpg,jpeg,png,svg|max:2000';

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;

                return back()->with(['dismiss' => $errors[0]]);
            }
        }


        $response = $this->settingService->siteSettingsUpdate($request);
        if($response['success'])
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function siteTextSettings()
    {
        $data['title'] = __('Knowledge Base and Support Site Text Update');
        return view('knowledgebase::admin.site-settings.text-settings',$data);
    }

    public function siteTextSettingsUpdate(Request $request)
    {
        $response = $this->settingService->saveSiteTextSetting($request);
        if($response['success'])
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function siteTextReset()
    {
        $response = $this->settingService->siteTextReset();
        if($response['success'])
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function siteIconList()
    {
        $data['title'] = __('Icon List');
        $response = $this->settingService->siteIconList();
        if($response['success'])
        {
            $data['icon_list'] = $response['data'];
        }
        return view('knowledgebase::admin.site-settings.icon-list',$data);
    }

    public function siteIconAdd()
    {
        $data['title'] = __('Add new Icon');

        return view('knowledgebase::admin.site-settings.icon-add',$data);
    }

    public function siteIconUpdate(IconRequest $request)
    {
        $response = $this->settingService->saveSiteIcon($request);
        if($response['success'])
        {
            return redirect()->route('knowledgebase_site_icon_list')->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function siteIconEdit($id)
    {
        $data['title'] = __('Update Icon Details');
        $response = $this->settingService->siteIconDetailsById($id);
        if($response['success'])
        {
            $data['item'] = $response['data'];
        }

        return view('knowledgebase::admin.site-settings.icon-add',$data);
    }

    public function siteIconDelete($id)
    {
        $response = $this->settingService->siteIconDelete($id);
        if($response['success'])
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }

}
