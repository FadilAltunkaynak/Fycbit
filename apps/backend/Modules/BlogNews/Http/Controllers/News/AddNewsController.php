<?php

namespace Modules\BlogNews\Http\Controllers\News;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BlogNews\Entities\NewsCategory;
use Modules\BlogNews\Http\Services\NewsService;
use Modules\BlogNews\Http\Requests\News\NewsCreateProcess;
use App\Http\Services\AdminLangService;
use Modules\BlogNews\Entities\NewsPost;
use Modules\BlogNews\Http\Requests\NewsPostTranslationRequest;

class AddNewsController extends Controller
{
    private $service;
    private $languageService;
    public function __construct()
    {
        $this->service = new NewsService();
        $this->languageService = new AdminLangService;
    }   

   public function allNewsPage()
   {
        $data = [];
        try {
            $data['title'] = __("News List");
            $data['category'] = NewsCategory::where(['sub'=> 0])->get();
        } catch (\Exception $e) {
            storeException('AddNews',$e->getMessage());
            return view('blognews::news.create.list',[]);
        }
        return view('blognews::news.create.list',$data);
        
   }

   public function newsPostData($type, $main, $sub)
   {
        try {
            $posts = $this->service->getNewsPosts($type, $main, $sub);
            return datatables()->of($posts['data'])
                ->addColumn('thumbnail', function ($query) {
                    $html = '<img src="' . $query->thumbnail . '" alt="'.__('No Image Found').'" height="50px" />';
                    return $html;
                })
                ->addColumn('title', function ($query) {
                    return $query->title;
                })
                ->addColumn('published', function ($query) {
                    if($query->publish == STATUS_ACTIVE){
                        return '<span class="btn btn-sm btn-primary">'.__('Yes').'</span>';
                     }
                    return '<span class="btn btn-sm btn-danger">'.__('No').'</span>';
                })
                ->addColumn('status', function ($query) {
                    if($query->status == STATUS_ACTIVE){
                       return '<span class="btn btn-sm btn-primary">'.__('Active').'</span>';
                    }
                    return '<span class="btn btn-sm btn-danger">'.__('Inactive').'</span>';
                })
                ->addColumn('translation', function ($query) {
                    return translationActionButtonBlogNews('newsPostTranslatePage',$query->id);
                })
                ->addColumn('actions', function ($query) {
                    return ActionButtonForNewsList($query->post_id);
                })
                ->rawColumns(['thumbnail','published','status','translation','actions'])
                ->make(true);
        } catch (\Exception $e) {
            storeException('blogPostData',$e->getMessage());
            return responseData('blogPostData',__('Something went wrong'));
        }
   }

   public function createNewsPage($slug = '')
   {
        $data = [];
        try {
            $data['title'] = __("Create News");
            if($slug){
                $data['title'] = __("Update News");
                $blog = $this->service->getNewsData($slug);
                $data['news'] = $blog['data'] ?? [];

                $subCat = $this->service->getSubCategory($data['news']);
                $data['sub_category'] = $subCat['data'] ?? [];
            }
            $cats = $this->service->getMainCategory();
            $data['category'] = $cats['data'] ?? [];
        } catch (\Exception $e) {
            storeException('AddNews',$e->getMessage());
        }
        return view('blognews::news.create.create',$data);
   }

   public function createNewsProcess(NewsCreateProcess $request)
   {
        try {
            $response = $this->service->createNewsProcess($request);
            if($response['success']) 
                return redirect()->route('allNewsPage')->with('success',$response['message']);
            return redirect()->route('allNewsPage')->with('dismiss',$response['message']);
        } catch (\Exception $e) {
            storeException('createNewsProcess',$e->getMessage());
            return redirect()->route('allNewsPage')->with('dismiss',__('Something went wrong'));
        }
        
   }

   public function deleteNewsProcess($slug)
   {
        try {
            $response = $this->service->deleteNewsProcess($slug);
            if($response['success']) 
                return redirect()->route('allNewsPage')->with('success',$response['message']);
            return redirect()->route('allNewsPage')->with('dismiss',$response['message']);
        } catch (\Exception $e) {
            storeException('deleteNewsProcess',$e->getMessage());
            return redirect()->route('allNewsPage')->with('dismiss',__('Something went wrong'));
        }
        
   }

   public function getNewsSubCategorys($id)
   {
        try {
            $response = $this->service->getSubCategorys($id);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('getSubCategorys',$e->getMessage());
            return response()->json(responseData(false,$e->getMessage()));
        }
        
   }

    public function newsTranslatePage($id)
    {
        $data = [];
        try {
            $data['title'] = __("Update Languages News Post");
            $data['news_post_details'] = NewsPost::with(['translationNewsPost'])->find(decrypt($id));
            $language_response = $this->languageService->activeLanguageList();
            if($language_response['success'])
            {
                $data['language_list'] = $language_response['data'];
            }
            
        } catch (\Exception $e) {
            storeException('CategoryPage',$e->getMessage());
        }
        return view('blognews::news.create.news-translate',$data);
    }

    public function newsTranslateUpdatePage($id, $lang_key)
    {
        $data = [];
        try {
            $data['title'] = __("Language Update News");
            $news_post_details = NewsPost::find(decrypt($id));
            
            $language_details_response = $this->languageService->languageDetailsByKey($lang_key);

            if(isset($news_post_details) && $language_details_response['success']) {
                
                $data['news_post_details'] = $news_post_details;
                $data['language_details'] = $language_details_response['data'];

                $news_translation_response = $this->service->getNewsDetailsTranslationByLangKey($news_post_details->id, $data['language_details']->key);
                if($news_translation_response['success'])
                {
                    $data['news_post_translation'] = $news_translation_response['data'] ;
                }
                
                return view('blognews::news.create.news-translation-update',$data);
            }else{
                return back()->with(['success' => 'Invalid Request']);
            }
            
        } catch (\Exception $e) {
            storeException('CategoryPage',$e->getMessage());
        }
        return back()->with(['success' => 'Invalid Request']);
    }

    public function newsTranslateUpdateText(NewsPostTranslationRequest $request)
    {
        $response = $this->service->updateLanguageForNewsPost($request);
        if($response['success'])
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }
}
