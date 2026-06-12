<?php

namespace Modules\BlogNews\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BlogNews\Http\Services\BlogService;
use Modules\BlogNews\Http\Requests\Api\SearchRequest;
use Modules\BlogNews\Http\Requests\Api\BlogNewsRequest;

class BlogController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new BlogService();
    }

    public function getBlog(BlogNewsRequest $request)
    {
        try {
            $lang_key = $request->header('lang') ?? 'en';
            if(isset($request->category)){
                $id = decryptId($request->category);
                if (isset($id['success']))
                    $id = 0;
                $request->merge(['category' => $id]);
            }
            if(isset($request->subcategory)){
                $id = decryptId($request->subcategory);
                if (isset($id['success']))
                    $id = 0;
                $request->merge(['subcategory' => $id]);
            }

            $type = (( isset($request->category) && $request?->subcategory == 0)
                ? TYPE_MAIN_CATEGORY : (isset($request->subcategory) ? TYPE_SUB_CATEGORY : null));

            $resposne = $this->service->getBlogPosts($type, $request->category ?? null, $request->subcategory ?? null, 
            [ 
                'type' => $request->type ?? TYPE_BLOG_RECENT, 
                'api' => true, 
                'limit' => $request->limit ?? 0,
                'lang_key' =>$lang_key
            ]);
            
            
            return response()->json($resposne);
        } catch (\Exception $e) {
            storeException(false, $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function getBlogDetails(Request $request)
    {
        try {
            $slug = $request->id ?? '';
            $lang_key = $request->header('lang') ?? 'en';
            $resposne = $this->service->getBlogDetails($slug,$lang_key);
            return response()->json($resposne);
        } catch (\Exception $e) {
            storeException('getBlogDetails', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }
    

    public function getCategory(Request $request)
    {
        try {
            $resposne = $this->service->getApiCategory($request);

            return response()->json($resposne);
        } catch (\Exception $e) {
            storeException(false, $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function blogSearch(SearchRequest $request)
    {
        try {
            $extra['api'] = true;
            $resposne = $this->service->blogSearch($request, $extra);
            return response()->json($resposne);
        } catch (\Exception $e) {
            storeException('blogSearch', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }
}
