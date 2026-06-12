<?php

namespace Modules\BlogNews\Http\Services;

use Illuminate\Http\Request;
use Modules\BlogNews\Entities\BlogPost;
use Modules\BlogNews\Entities\NewsPost;
use Modules\BlogNews\Entities\BlogComment;
use Modules\BlogNews\Entities\NewsComment;
use Modules\BlogNews\Entities\BlogNewsSetting;
use Modules\BlogNews\Repository\CommentRepository;

class CommentService{
    private $repo;
    public function __construct()
    {
        $this->repo = new CommentRepository();
    }

    public function storeComment($request, $slug = '')
    {
        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'website' => $request->website ?? NULL,
                'message' => $request->message,
                'post_id' => $request->post_id,
                'status' => STATUS_ACTIVE
            ];
            if(isset($request->to)){
                $data['is_reply'] = true;
                $data['reply_to'] = $request->to;
            }
            $setting = BlogNewsSetting::get()->toSlugValue();
            $checkSetting = isset($request->type) && $request->type == 'blog' ?
                     'blog_auto_comment_approval' : 'news_auto_comment_approval';

            if ($setting->$checkSetting ?? true)
                $data['status'] = STATUS_ACTIVE;
            else 
                $data['status'] = STATUS_PENDING;

            $model = isset($request->type) && $request->type == 'blog' ?
                     BlogComment::class : NewsComment::class;
            return $this->repo->storeComment($data, $slug, $model);
        } catch (\Exception $e) {
            storeException('storeComment service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getComment($requset ,$type)
    {
        try {
            $model = $type == 'blog' ? BlogComment::class : NewsComment::class ;
            $response = $this->repo->getComment($model, ['post_id' => $requset->post_id ?? null ,'limit' => $requset->limit ?? 0]);
            return $response;
        } catch (\Exception $e) {
            storeException(false,__("Something went wrong"));
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getPendingBlogComment()
    {
        try {
            return $this->repo->getPendingBlogComment(); 
        } catch (\Exception $e) {
            storeException('getPendingBlogComment', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    
    public function getPendingNewsComment()
    {
        try {
            return $this->repo->getPendingNewsComment(); 
        } catch (\Exception $e) {
            storeException('getPendingNewsComment', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function acceptComment($slug, $model)
    {
        try {
            return $this->repo->acceptComment($slug, $model);
        } catch (\Exception $e) {
            storeException('acceptComment', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function deleteComment($id, $model)
    {
        try {
            return $this->repo->deleteComment($id, $model);
        } catch (\Exception $e) {
            storeException('deleteComment service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    
    public function BlogCommentEdit($request)
    {
        try {
            $id = isset($request->id) ? $request->id : 0;
            $id = decryptId($id);
            if(isset($id['success'])) return responseData(false, __("Comment id is invalid"));
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'website' => $request->website,
                'message' => $request->message,
            ];
            return $this->repo->storeComment($data,$id, BlogComment::class);
        } catch (\Exception $e) {
            storeException('BlogCommentEdit service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    public function getPostDetails($slug)
    {
        try {
            $post = BlogPost::where('slug', $slug)->first();
            if ($post) return $post;
            return false;
        } catch (\Exception $e) {
            storeException('getPostDetails service', $e->getMessage());
            return false;
        }
    }
    public function getNewsDetails($slug)
    {
        try {
            $post = NewsPost::where('slug', $slug);
            if ($post) return $post;
            return false;
        } catch (\Exception $e) {
            storeException('getNewsDetails service', $e->getMessage());
            return false;
        }
    }

    public function BlogCommentList($slug)
    {
        try {
            return $this->repo->blogCommentList($slug);
        } catch (\Exception $e) {
            storeException('BlogCommentList service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    public function NewsCommentList($slug)
    {
        try {
            return $this->repo->newsCommentList($slug);
        } catch (\Exception $e) {
            storeException('NewsCommentList service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    public function NewsCommentEdit($request)
    {
        try {
            $id = isset($request->id) ? $request->id : 0;
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'website' => $request->website,
                'message' => $request->message,
            ];
            return $this->repo->storeComment($data,$id, NewsComment::class);
        } catch (\Exception $e) {
            storeException('NewsCommentEdit service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}