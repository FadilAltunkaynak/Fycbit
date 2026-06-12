<?php

namespace Modules\BlogNews\Http\Services;

use Illuminate\Support\Facades\DB;
use Modules\BlogNews\Entities\NewsPost;
use Modules\BlogNews\Entities\NewsCategory;
use Modules\BlogNews\Repository\NewsRepository;

class NewsService {

    private $repo;
    public function __construct(){
        $this->repo = new NewsRepository();
    }

    public function getNewsPosts($type, $main, $sub, $ex = [])
    {
        try{
            $blogPosts = $this->repo->getNewsPosts($type, $main, $sub, $ex);
            return $blogPosts;
        } catch (\Exception $e) {
            storeException('getBlogPosts',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function createNewsProcess($request)
    {
        try {
            $slug = isset($request->slug) ? $request->slug : '';
            if($request->hasFile('thumbnail')){
                if(isset($request->id)){
                    $blog = $this->repo->findNews($slug);
                    if($blog['success']){
                        $thumbnail = uploadimage($request->thumbnail,NEWS_THUMBNAIL_PATH,$blog['data']->thumbnail);
                    }
                }else
                $thumbnail = uploadimage($request->thumbnail,NEWS_THUMBNAIL_PATH);
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
                'comment_allow' => $request->comment_allow ?? 0
            ];

            if(isset($request->publish) && $request->publish == __("Publish")){ 
                $data['publish_at'] = date('Y-m-d H:i:s');
                $data['publish'] = STATUS_ACTIVE;
            }else if(isset($request->publish_at) && empty(!$request->publish_at)){
                $data['publish_at'] = date('Y-m-d H:i:s', strtotime($request->publish_at));
                $data['publish'] = STATUS_ACTIVE;
            }

            if($thumbnail ?? false) $data['thumbnail'] = $thumbnail;
            return $this->repo->newsSave($data,$slug);
        }catch(\Exception $e){
            storeException('createNewsProcess service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }

    public function deleteNewsProcess($slug)
    {
        try {
            $blog = $this->repo->findNews($slug);
            if (!$blog['success'])
                return $blog;
            $blog = $blog['data'] ?? [];
            if(file_exists(NEWS_THUMBNAIL_PATH.($blog->thumbnail ?? '')) && !is_dir(NEWS_THUMBNAIL_PATH.($blog->thumbnail ?? ''))){
                unlink(NEWS_THUMBNAIL_PATH . ($blog->thumbnail ?? ''));
            }
            if ($blog->delete())
                return responseData(true, __("Post deleted successfully"));
            else    
                return responseData(false, __("Post deleted failed"));
        }catch(\Exception $e){
            storeException('createNewsProcess service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }

    public function getSubCategorys($id)
    {
        try {
            $data = NewsCategory::where(['main_id' => $id, 'status' => STATUS_ACTIVE])->get();
            return responseData(true,__("Category get successfully"),$data);
        }catch(\Exception $e){
            storeException('newsGetSubCategorys service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }

    public function getMainCategory()
    {
        try {
            return $this->repo->getMainCategory();
        }catch(\Exception $e){
            storeException('newsGetMainCategory service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }

    public function getNewsData($id)
    {
        try {
            return $this->repo->getNewsData($id);
        }catch(\Exception $e){
            storeException('newsGetBlogData service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }
    public function getSubCategory($sub)
    {
        try {
            return $this->repo->getSubCategory($sub);
        }catch(\Exception $e){
            storeException('newsGetSubCategory service',$e->getMessage());
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
            $id = decrypt($id) ?? 0 ;
            $data = NewsCategory::find($id);
            $post = NewsPost::where('category', $id ?? 0)->get()->count();
            if ($post > 0) return responseData(false, __("Please remove news post under this category to delete this category"));
            $data->delete();
            DB::commit();
            return responseData(true,__("Category deleted successfully"));
        }catch(\Exception $e){
            DB::rollBack();
            storeException('deleteCategory service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }

    public function getApiCategory($request)
    {
        try {
            $lang_key = $request->header('lang')??'en';
            $data = NewsCategory::with(['translationCategory'=>function($query) use($lang_key){
                
                $query->where('lang_key', $lang_key);
                
            }])->where('status', STATUS_ACTIVE)->get();
            
            $category = [];
            foreach($data as $cats){
                if($cats->sub) continue;
                $ct_title = null;

                if($cats->translationCategory->count() > 0) {
                    $ct_title = $cats->translationCategory[0]->title;
                    
                    $row = [
                    'id' => encrypt($cats->id),
                    'title' => $ct_title,
                    // 'sub' => []
                ];
                    
                }else{
                    $row = [
                        'id' => encrypt($cats->id),
                        'title' => $cats->title,
                        // 'sub' => []
                    ];
                }

                foreach ($data as $cat) {
                    if (!$cat->sub) continue;
                    if ($cats->id == $cat->main_id) {
                        // $row['sub'][] = [
                        //     'id' => encrypt($cat->id),
                        //     'title' => $cat->title,
                        // ];
                    }
                }
                $category[] = (Object) $row;
            }
            return responseData(true,__("Category get successfully"),$category);
        }catch(\Exception $e){
            storeException('getApiCategory news service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }

    public function getNewsDetails($slug, $lang_key = null)
    {
        try{
            $blogPosts = $this->repo->getNewsDetails($slug, $lang_key);
            return $blogPosts;
        } catch (\Exception $e) {
            storeException('getBlogDetails',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function newsSearch($request)
    {
        try {
            return $this->repo->newsSearch($request);
        }catch(\Exception $e){
            storeException('newsSearch service',$e->getMessage());
            return responseData(false,$e->getMessage());
        }
    }
    public function getCategoryDetailsTranslationByLangKeyNews($category_id, $lang_key)
    {
        $response = $this->repo->getCategoryDetailsTranslationByLangKeyNews($category_id, $lang_key);
        return $response;
    }

    public function updateLanguageForCategory($request)
    {
        $response = $this->repo->updateLanguageForCategory($request);
        return $response;
    }

    public function getNewsDetailsTranslationByLangKey($news_post_id, $lang_key)
    {
        $response = $this->repo->getNewsDetailsTranslationByLangKey($news_post_id, $lang_key);
        return $response;
    }

    public function updateLanguageForNewsPost($request)
    {
        $response = $this->repo->updateLanguageForNewsPost($request);
        return $response;
    }
}