<?php

namespace Modules\BlogNews\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BlogNews\Http\Services\NewsService;
use Modules\BlogNews\Http\Requests\Api\SearchRequest;
use Modules\BlogNews\Http\Requests\Api\BlogNewsRequest;

class NewsController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new NewsService();
    }

    public function getNews(BlogNewsRequest $request)
    {
        try {
            $lang_key = $request->header('lang') ?? 'en';
            if(isset($request->category)){
                $id = decryptId($request->category);
                if (isset($id['success']))
                    $id = 0;
                $request->merge(['category' => $id]);
            }
            // if(isset($request->subcategory)){
            //     $id = decryptId($request->subcategory);
            //     if (isset($id['success']))
            //         $id = 0;
            //     $request->merge(['subcategory' => $id]);
            // }
            // $type = ($request->subcategory ?? 0) == 0
            //     ? TYPE_MAIN_CATEGORY : TYPE_SUB_CATEGORY;
            $type = (( isset($request->category) && $request?->subcategory == 0)
                ? TYPE_MAIN_CATEGORY : (isset($request->subcategory) ? TYPE_SUB_CATEGORY : null));
            
            $response = $this->service->getNewsPosts($type, $request->category ?? 0, $request->subcategory ?? 0, 
            [ 
                'type' => $request->type ?? TYPE_NEWS_RECENT,
                'api' => true, 
                'limit' => $request->limit ?? 0 ,
                'lang_key' =>$lang_key
            ]);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException(false, $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function getCategory(Request $request)
    {
        try {
            $response = $this->service->getApiCategory($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException(false, $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }
    public function getNewsDetails(Request $request)
    {
        try {
            $slug = $request->id ?? '';
            $lang_key = $request->header('lang') ?? 'en';
            $resposne = $this->service->getNewsDetails($slug, $lang_key);
            return response()->json($resposne);
        } catch (\Exception $e) {
            storeException('getNewsDetails', $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }
    public function newsSearch(SearchRequest $request)
    {
        try {
            $resposne = $this->service->newsSearch($request);
            return response()->json($resposne);
        } catch (\Exception $e) {
            storeException('newsSearch', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }
}
