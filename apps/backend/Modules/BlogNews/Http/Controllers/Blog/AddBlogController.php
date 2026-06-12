<?php

namespace Modules\BlogNews\Http\Controllers\blog;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Nwidart\Modules\Facades\Module;
use Modules\BlogNews\Entities\BlogCategory;
use Modules\BlogNews\Http\Services\BlogService;
use Modules\BlogNews\Http\Requests\Blog\BlogCreateProcess;
use App\Http\Services\AdminLangService;
use Modules\BlogNews\Http\Requests\BlogPostTranslationRequest;

class AddBlogController extends Controller
{
    private $service;
    private $languageService;
    public function __construct()
    {
        $this->service = new BlogService();
        $this->languageService = new AdminLangService;
    }   

   public function allBlogPage()
   {
        $data = [];
        try {
            $data['title'] = __("Blogs List");
            $data['category'] = BlogCategory::where(['sub'=> 0, 'status'=> STATUS_ACTIVE]);
            $data['category'] = $data['category']->get();
            //$data['posts'] = $this->service->getBlogPosts();
        } catch (\Exception $e) {
            storeException('AddBlog',$e->getMessage());
            return view('blognews::blog.create.list',[]);
        }
        return view('blognews::blog.create.list',$data);
        
   }

   public function blogPostData($type, $main, $sub)
   {
        try {
            $posts = $this->service->getBlogPosts($type, $main, $sub);
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
                        
                    return translationActionButtonBlogNews('blogTranslatePage',$query->id);
                    
                })
                ->addColumn('actions', function ($query) {
                    return ActionButtonForBlogList($query->post_id);
                })
                ->rawColumns(['thumbnail','published','status','translation','actions'])
                ->make(true);
        } catch (\Exception $e) {
            storeException('blogPostData',$e->getMessage());
            return responseData('blogPostData',__('Something went wrong'));
        }
   }

   public function createBlogPage($slug = '')
   {
        $data = [];
        try {
            $data['title'] = __("Create Blog");
            if($slug){
                $data['title'] = __("Update Blog");
                $blog = $this->service->getBlogData($slug);
                $data['blog'] = $blog['data'] ?? [];

                $subCat = $this->service->getSubCategory($data['blog']);
                $data['sub_category'] = $subCat['data'] ?? [];
            }
            $cats = $this->service->getMainCategory();
            $data['category'] = $cats['data'] ?? [];
            if(!($data['category']->count() > 0))
                return redirect()->back()->with('dismiss', __('Create main category to create blog'));
        } catch (\Exception $e) {
            storeException('AddBlog',$e->getMessage());
        }
        return view('blognews::blog.create.create',$data);
   }

   public function createBlogProcess(BlogCreateProcess $request)
   {
        try {
            $response = $this->service->createBlogProcess($request);
            if($response['success']) 
                return redirect()->route('allBlogPage')->with('success',$response['message']);
            return redirect()->route('allBlogPage')->with('dismiss',$response['message']);
        } catch (\Exception $e) {
            storeException('createBlogProcess',$e->getMessage());
            return redirect()->route('allBlogPage')->with('dismiss',__('Something went wrong'));
        }
        
   }

   public function deleteBlogProcess($slug)
   {
        try {
            $response = $this->service->deleteBlogProcess($slug);
            if($response['success']) 
                return redirect()->route('allBlogPage')->with('success',$response['message']);
            return redirect()->route('allBlogPage')->with('dismiss',$response['message']);
        } catch (\Exception $e) {
            storeException('deleteBlogProcess',$e->getMessage());
            return redirect()->route('allBlogPage')->with('dismiss',__('Something went wrong'));
        }
        
   }

   public function getSubCategorys($id)
   {
        try {
            $response = $this->service->getSubCategorys($id);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('getSubCategorys',$e->getMessage());
            return response()->json(responseData(false,$e->getMessage()));
        }
        
   }

    public function blogTranslatePage($id)
    {
        $data = [];
        try {
            $data['title'] = __("Update Languages For Blog Post");
            
            $blog_response = $this->service->getBlogPostDetails(decrypt($id));
            if($blog_response['success'])
            {
                $data['blog_post_details'] = $blog_response['data'];
                
            }

            $language_response = $this->languageService->activeLanguageList();
            if($language_response['success'])
            {
                $data['language_list'] = $language_response['data'];
            }
        } catch (\Exception $e) {
            storeException('CategoryPage',$e->getMessage());
        }
        return view('blognews::blog.create.blog-translate',$data);
    }

    public function blogTranslateUpdatePage($id, $lang_key)
    {
        $data = [];
        try {
            $data['title'] = __("Language Update Blog");
            $blog_response = $this->service->getBlogPostDetails(decrypt($id));
            
            $language_details_response = $this->languageService->languageDetailsByKey($lang_key);

            if($blog_response['success'] && $language_details_response['success']) {
                
                $blog_post_details = $blog_response['data'];
                $data['blog_post_details'] = $blog_post_details;
                $data['language_details'] = $language_details_response['data'];

                $category_translation_response = $this->service->getBlogDetailsTranslationByLangKey($blog_post_details->id, $data['language_details']->key);
                if($category_translation_response['success'])
                {
                    $data['blog_post_translation'] = $category_translation_response['data'] ;
                }
                
                return view('blognews::blog.create.blog-translation-update',$data);
            }else{
                return back()->with(['success' => 'Invalid Request']);
            }
            
        } catch (\Exception $e) {
            storeException('CategoryPage',$e->getMessage());
        }
        return back()->with(['success' => 'Invalid Request']);
    }

    public function blogTranslateUpdateText(BlogPostTranslationRequest $request)
    {
        $response = $this->service->updateLanguageForBlogPost($request);
        if($response['success'])
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }
}
