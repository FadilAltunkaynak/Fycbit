<?php

namespace Modules\BlogNews\Http\Services;

use Illuminate\Support\Facades\DB;
use Modules\BlogNews\Entities\BlogPost;
use Modules\BlogNews\Entities\BlogCategory;
use Modules\BlogNews\Repository\BlogRepository;

class BlogService {

    private $repo;
    public function __construct(){
        $this->repo = new BlogRepository();
    }

    public function getBlogPosts($type, $main, $sub, $ex = [])
    {
        try{
            $blogPosts = $this->repo->getBlogPosts($type, $main, $sub, $ex);
            return $blogPosts;
        } catch (\Exception $e) {
            storeException('getBlogPosts',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }
    
    public function getBlogDetails($slug, $lang_key = null)
    {
        try{
            $blogPosts = $this->repo->getBlogDetails($slug, $lang_key);
            return $blogPosts;
        } catch (\Exception $e) {
            storeException('getBlogDetails',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function createBlogProcess($request)
    {
        try {
            $id = isset($request->slug) ? $request->slug : "";
            if($request->hasFile('thumbnail')){
                if(isset($request->id)){
                    $blog = $this->repo->findBlog($id);
                    if($blog['success']){
                        $thumbnail = uploadimage($request->thumbnail,BLOG_THUMBNAIL_PATH,$blog['data']->thumbnail);
                    }
                }else
                $thumbnail = uploadimage($request->thumbnail,BLOG_THUMBNAIL_PATH);
            }
            $data = [
                'title' => $request->title,
                'slug' => make_unique_slug($request->title),
                'category' => $request->category,
                'sub_category' => $request->sub_category,
                'status' => $request->status,
                'body' => preg_replace('/\.\.\/\.\.\//',asset(''), $request->body),
                'keywords' => $request->keywords ?? NULL,
                'description' => $request->description ?? NULL,
                'is_fetured' => $request->is_fetured ?? 0,
                'comment_allow' => $request->comment_allow ?? 0
            ]; 

            if(isset($request->publish) && $request->publish == __("Publish")){ 
                $data['publish_at'] = date('Y-m-d H:i:s');
                $data['publish'] = STATUS_ACTIVE;
            }else if(isset($request->publish_at) && empty(!$request->publish_at)){
                $data['publish_at'] = date('Y-m-d H:i:s', strtotime($request->publish_at));
                $data['publish'] = STATUS_ACTIVE;
            }
            //dd(date('Y-m-d'), $data['publish_at']);
            if($thumbnail ?? false) $data['thumbnail'] = $thumbnail;
            return $this->repo->blogSave($data,$id);
        }catch(\Exception $e){
            storeException('createBlogProcess service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }

    public function deleteBlogProcess($slug)
    {
        try {
            $blog = $this->repo->findBlog($slug);
            if (!$blog['success'])
                return $blog;
            $blog = $blog['data'] ?? [];
            if(file_exists(BLOG_THUMBNAIL_PATH.($blog->thumbnail ?? '') && !is_dir(BLOG_THUMBNAIL_PATH.($blog->thumbnail ?? '')))){
                unlink(BLOG_THUMBNAIL_PATH.($blog->thumbnail ?? ''));
            }
            if ($blog->delete())
                return responseData(true, __("Post deleted successfully"));
            else    
                return responseData(false, __("Post deleted failed"));
        }catch(\Exception $e){
            storeException('createBlogProcess service',$e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function getSubCategorys($id)
    {
        try {
            $data = BlogCategory::where(['main_id' => $id, 'status' => STATUS_ACTIVE])->get();
            return responseData(true,__("Category get successfully"),$data);
        }catch(\Exception $e){
            storeException('createBlogProcess service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }
    
    public function getApiCategory($request)
    {
        try {
            $lang_key = $request->header('lang')??'en';
            
            $data = BlogCategory::with(['translationCategory'=>function($query) use($lang_key){
                
                $query->where('lang_key', $lang_key);
                
            }])->where('status', STATUS_ACTIVE)->get();
            $category = [];
            foreach($data as $key=>$cats){
                
                if($cats->sub) continue;
                $ct_title = null;
                if($cats->translationCategory->count() > 0) {
                    $ct_title = $cats->translationCategory[0]->title;
                    
                    $row = [
                        'id' => encrypt($cats->id),
                        'title' => $ct_title,
                        'sub' => []
                    ];
                    
                }else{
                    $row = [
                        'id' => encrypt($cats->id),
                        'title' => $cats->title,
                        'sub' => []
                    ];
                }
                
                foreach ($data as $cat) {
                    if (!$cat->sub) continue;
                    if ($cats->id == $cat->main_id) {
                        
                        if($cat->translationCategory->count() > 0)
                        {
                            $sct_details = $cat->translationCategory[0];
                            if($sct_details->blog_category_id == $cat->id) {
                                
                                $sct_title = $sct_details->title;
                                $row['sub'][] = [
                                    'id' => encrypt($cat->id),
                                    'title' => $sct_title,
                                ];
                            }else{
                                $row['sub'][] = [
                                    'id' => encrypt($cat->id),
                                    'title' => $cat->title,
                                ];
                            }
                        }else{
                            $row['sub'][] = [
                                'id' => encrypt($cat->id),
                                'title' => $cat->title,
                            ];
                        }
                        
                    }
                }
                $category[] = (Object) $row;
            }
            return responseData(true,__("Category get successfully"),$category);
        }catch(\Exception $e){
            storeException('createBlogProcess service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }

    public function getMainCategory()
    {
        try {
            return $this->repo->getMainCategory();
        }catch(\Exception $e){
            storeException('getMainCategory service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }
    public function blogSearch($request, $extra = [])
    {
        try {
            return $this->repo->blogSearch($request, $extra);
        }catch(\Exception $e){
            storeException('blogSearch service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }

    public function getBlogData($slug)
    {
        try {
            return $this->repo->getBlogData($slug);
        }catch(\Exception $e){
            storeException('getBlogData service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }
    public function getSubCategory($sub)
    {
        try {
            return $this->repo->getSubCategory($sub);
        }catch(\Exception $e){
            storeException('getSubCategory service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }

    public function addEditCat($request)
    {
        try {
            $id = isset($request->id) ? $request->id : 0 ;
            $data = [
                'title' => $request->title,
                'status' =>  $request->status,
            ];
            return $this->repo->addEditCat($data,$id);
        }catch(\Exception $e){
            storeException('addEditCat service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }
    public function addEditSubCat($request)
    {
        try {
            $id = isset($request->id) ? decrypt($request->id) : 0 ;
            $data = [
                'title' => $request->title,
                'main_id' => $request->category,
                'sub' => STATUS_ACTIVE,
                'status' =>  $request->status,
            ];
            return $this->repo->addEditSubCat($data,$id);
        }catch(\Exception $e){
            storeException('addEditSubCat service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }

    public function deleteCategory($id)
    {
        try {
            DB::beginTransaction();
            $data = BlogCategory::find($id);
            if(isset($data->sub) && !$data->sub){
                $cat = BlogCategory::where(['sub' => STATUS_ACTIVE,'main_id' => $data->id])->get()->count();
                if ($cat > 0) return responseData(false, __("Please remove sub categories under this category to delete this category"));
                BlogCategory::where('main_id', $data->id)->delete();
            }
            $post = BlogPost::where(['category' => $data->main_id ?? 0,'sub_category' => $id])->get()->count();
            if ($post > 0) return responseData(false, __("Please remove blog post under this sub category to delete this sub category"));
            $data->delete();
            DB::commit();
            return responseData(true,__("Category deleted successfully"));
        }catch(\Exception $e){
            DB::rollBack();
            storeException('deleteCategory service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }

    public function updateLanguageForCategory($request)
    {
        $response = $this->repo->updateLanguageForCategory($request);
        return $response;
    }

    public function updateLanguageForSubCategory($request)
    {
        $response = $this->repo->updateLanguageForSubCategory($request);
        return $response;
    }

    public function getCategoryDetailsTranslationByLangKey($category_id, $lang_key)
    {
        $response = $this->repo->getCategoryDetailsTranslationByLangKey($category_id, $lang_key);
        return $response;
    }

    public function getBlogPostDetails($id)
    {
        $response = $this->repo->getBlogPostDetails($id);
        return $response;
    }

    public function getBlogDetailsTranslationByLangKey($blog_post_id, $lang_key)
    {
        $response = $this->repo->getBlogDetailsTranslationByLangKey($blog_post_id, $lang_key);
        return $response;
    }

    public function updateLanguageForBlogPost($request)
    {
        $response = $this->repo->updateLanguageForBlogPost($request);
        return $response;
    }
}