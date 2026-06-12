<?php

namespace Modules\BlogNews\Http\Controllers\News;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
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
        try {
            $data['title'] = __("Pending News Comments");
            if($request->ajax()){
                $comments = $this->service->getPendingNewsComment();
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
                        return ActionButtonForCommentList($query,'NewsCommentEdit', 'NewsCommentAccept', 'NewsCommentDelete');
                    })
                    ->rawColumns(['actions'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            storeException('commentPage', $e->getMessage());
            return redirect()->back()->with('dismiss',__("Something went wrong"));
        }
        return view('blognews::news.comment.comment',$data);
    }

    public function NewsCommentAccept($id)
    {
        try {
            $response = $this->service->acceptComment($id, NewsComment::class);
            if($response['success'])
                return redirect()->back()->with('success', $response['message']);            
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('BlogCommentAccept', $e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }
    }

    public function NewsCommentDelete($id)
    {
        try {
            $response = $this->service->deleteComment($id, NewsComment::class);
            if($response['success'])
                return redirect()->back()->with('success', $response['message']);            
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('NewsCommentDelete', $e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }
    }
    
    public function NewsCommentEdit(CommentEditRequest $request)
    {
        try {
            $response = $this->service->NewsCommentEdit($request);
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
        $data['title'] = __("Blog Comments");
        try {
            if($request->ajax()){
                $response = $this->service->NewsCommentList($slug);
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
                        return ActionButtonForCommentList($query, 'NewsCommentEdit', 'NewsCommentAccept', 'NewsCommentDelete',0);
                    })
                    ->rawColumns(['actions'])
                    ->make(true);
            }
            $data['id'] = $slug;
            $post = $this->service->getNewsDetails($slug);
            if ($post) $data['post'] = $post->title ?? '';
        } catch (\Exception $e) {
            storeException('commentList news', $e->getMessage());
        }
        return view('blognews::news.comment.list', $data);
    }
}
