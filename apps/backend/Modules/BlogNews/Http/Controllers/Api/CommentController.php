<?php

namespace Modules\BlogNews\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BlogNews\Http\Services\CommentService;
use Modules\BlogNews\Http\Requests\Api\CommentStoreRequest;

class CommentController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new CommentService();
    }
    public function storeComment(CommentStoreRequest $requset)
    {
        try{
            $response = $this->service->storeComment($requset);
            return response()->json($response);
        }catch(\Exception $e){
            storeException(false, $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }
    
    public function getComment(Request $request)
    {
        try{
            $slug = $request->post_id ?? '';
            $request->merge(['post_id' => $slug]);
            $type = $request->segment(2);
            $response = $this->service->getComment($request ,$type);
            return response()->json($response);
        }catch(\Exception $e){
            storeException(false, $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }
}
