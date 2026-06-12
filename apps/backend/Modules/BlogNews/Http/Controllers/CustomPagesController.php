<?php

namespace Modules\BlogNews\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BlogNews\Entities\CustomBlogNewsPage;
use Modules\BlogNews\Http\Requests\CustomPageRequest;
use Modules\BlogNews\Http\Services\CustomPageService;

class CustomPagesController extends Controller
{
   private $service;

   public function __construct()
   {
        $this->service = new CustomPageService();
   }
   public function getBlogCustomPages(Request $request)
   {
        $data = [];
        try{
            if($request->ajax()){
                $pages = CustomBlogNewsPage::whereType(CUSTOM_PAGE_BLOG)->get();
                return datatables()->of($pages)
                ->addColumn('title', function ($query) {
                    return $query->title;
                })
                ->addColumn('status', function ($query) {
                    if($query->status == STATUS_ACTIVE){
                       return '<span class="btn btn-sm btn-primary">'.__('Active').'</span>';
                    }
                    return '<span class="btn btn-sm btn-danger">'.__('Inactive').'</span>';
                })
                ->addColumn('actions', function ($query) {
                    return ActionButtonForPageList($query->type, $query->id);
                })
                ->rawColumns(['status','actions'])
                ->make(true);
            }
            $data['include'] = 'blognews::layouts.blogSidebar';
            $data['route'] = 'BlogCustomPages';
            $data['type'] = CUSTOM_PAGE_BLOG;
        } catch (\Exception $e) {
            storeException('getBlogCustomPages',$e->getMessage());
        }
        return view('blognews::pages.customPages', $data);
   }
   public function getNewsCustomPages(Request $request)
   {
        $data = [];
        try{
            if($request->ajax()){
                $pages = CustomBlogNewsPage::whereType(CUSTOM_PAGE_NEWS)->get();
                
                return datatables()->of($pages)
                ->addColumn('title', function ($query) {
                    return $query->title;
                })
                ->addColumn('status', function ($query) {
                    if($query->status == STATUS_ACTIVE){
                       return '<span class="btn btn-sm btn-primary">'.__('Active').'</span>';
                    }
                    return '<span class="btn btn-sm btn-danger">'.__('Inactive').'</span>';
                })
                ->addColumn('actions', function ($query) {
                    return ActionButtonForPageList($query->type, $query->id);
                })
                ->rawColumns(['status','actions'])
                ->make(true);
            }
            $data['include'] = 'blognews::layouts.newsSidebar';
            $data['route'] = 'NewsCustomPages';
            $data['type'] = CUSTOM_PAGE_NEWS;
        } catch (\Exception $e) {
            storeException('getBlogCustomPages',$e->getMessage());
        }
        return view('blognews::pages.customPages', $data);
   }

   public function createCustomPages($type, $id = 0)
   {
        $data = [];
        try{
            if(!in_array($type,[ CUSTOM_PAGE_BLOG, CUSTOM_PAGE_NEWS ]))
                return redirect()->back()->with('dismiss', __("Type is invalid"));
            $data['inclue'] = ($type == CUSTOM_PAGE_BLOG) ? 'blognews::layouts.blogSidebar' : 'blognews::layouts.newsSidebar';
            if($id) $data['page'] =  $this->service->getEditPageData($type, decrypt($id))['data'] ?? [];
            $data['type'] = $type;
        } catch (\Exception $e) {
            storeException('createCustomPages',$e->getMessage());
        }
        return view('blognews::pages.addEdit', $data);
   }

   public function createCustomPagesProcess(CustomPageRequest $request)
   {
        try{
            $route = isset($request->type) &&
                     $request->type == CUSTOM_PAGE_BLOG ? 
                     'BlogCustomPages' : 'NewsCustomPages';

            $response = $this->service->customPagesProcess($request);
            if($response['success'])
                return redirect()->route($route)->with('success', $response['message']);
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('createCustomPagesProcess',$e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }
   }
   public function customPagesDelete($type, $id)
   {
        try{
            $route = isset($type) &&
                     $type == CUSTOM_PAGE_BLOG ? 
                     'BlogCustomPages' : 'NewsCustomPages';
            $response = $this->service->customPagesDelete($type, $id);
            if($response['success'])
                return redirect()->route($route)->with('success', $response['message']);
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('createCustomPagesProcess',$e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }
   }

}
