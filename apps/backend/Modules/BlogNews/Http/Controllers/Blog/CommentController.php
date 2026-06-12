<?php

namespace Modules\BlogNews\Http\Controllers\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BlogNews\Entities\BlogComment;
use Modules\BlogNews\Entities\NewsComment;
use Modules\BlogNews\Http\Services\CommentService;
use Modules\BlogNews\Http\Requests\CommentEditRequest;

class CommentController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new CommentService();
    }

    public function commentPage(Request $request)
    {
        $data = [];
        $data['title'] = __("Pending Blog Comments");
        try {
            if($request->ajax()){
                $comments = $this->service->getPendingBlogComment();
                return datatables()->of($comments['data'])
                    ->addColumn('name', function ($query) {
                        return $query->name;
                    })
                    ->addColumn('email', function ($query) {
                        return $query->email;
                    })
                    ->addColumn('website', function ($query) {
                        return $query->website;
                    })
                    ->addColumn('message', function ($query) {
                        return $query->message;
                    })
                    ->addColumn('actions', function ($query) {
                        return ActionButtonForCommentList($query);
                    })
                    ->rawColumns(['actions'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            storeException('commentPage', $e->getMessage());
            return redirect()->back()->with('dismiss',__("Something went wrong"));
        }
        return view('blognews::blog.comment.comment',$data);
    }

    public function BlogCommentAccept($id)
    {
        try {
            $response = $this->service->acceptComment($id, BlogComment::class);
            if($response['success'])
                return redirect()->back()->with('success', $response['message']);            
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('BlogCommentAccept', $e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }
    }
    
    public function BlogCommentDelete($id, Request $request)
    {
        try {
            $id = decryptId($id);
            if(isset($id['success'])) 
                return redirect()->back()->with('dismiss', __("Comment id is invalid"));

            $response = $this->service->deleteComment($id, BlogComment::class);
            if($response['success'])
                return redirect()->back()->with('success', $response['message']);            
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('BlogCommentDelete', $e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }
    }
    
    public function BlogCommentEdit(CommentEditRequest $request)
    {
        try {
            $response = $this->service->BlogCommentEdit($request);
            if($response['success'])
                return redirect()->back()->with('success', $response['message']);            
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('BlogCommentEdit', $e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }
    }

    public function commentList($slug, Request $request)
    {
        $data = [];
        $data['title'] = __("Blog Comments List");
        try {
            if($request->ajax()){
                $response = $this->service->BlogCommentList($slug);
                if(!$response['success']) return response()->json($response);
                $comments = $response['data'];
                return datatables()->of($comments)
                    ->addColumn('name', function ($query) {
                        return $query->name;
                    })
                    ->addColumn('email', function ($query) {
                        return $query->email;
                    })
                    ->addColumn('website', function ($query) {
                        return $query->website;
                    })
                    ->addColumn('message', function ($query) {
                        return $query->message;
                    })
                    ->addColumn('actions', function ($query) {
                        return ActionButtonForCommentList($query, 'BlogCommentEdit', 'BlogCommentAccept', 'BlogCommentDelete',0);
                    })
                    ->rawColumns(['actions'])
                    ->make(true);
            }
            $data['id'] = $slug;
            $post = $this->service->getPostDetails($slug);
            if ($post) $data['post'] = $post->title ?? '';
        } catch (\Exception $e) {
            storeException('commentList', $e->getMessage());
        }
        return view('blognews::blog.comment.list', $data);
    }
}
