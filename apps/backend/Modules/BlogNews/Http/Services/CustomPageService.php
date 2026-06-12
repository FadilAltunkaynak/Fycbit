<?php

namespace Modules\BlogNews\Http\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Modules\BlogNews\Repository\CustomPageRepository;

class CustomPageService {

    private $repo;
    public function __construct(){
        $this->repo = new CustomPageRepository();
    }

    public function getEditPageData($type, $id)
    {
        try{
            $blogPosts = $this->repo->getEditPageData($type, $id);
            return $blogPosts;
        } catch (\Exception $e) {
            storeException('getEditPageData',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function customPagesProcess($request)
    {
        try{
            $id = isset($request->id) ? decrypt($request->id) : 0;

            $data = [
                'title' => $request->title,
                'type' => $request->type,
                'slug' => Str::slug($request->title,'-'),
                'status' => $request->status,
                'body' => $request->body,
            ];

            $blogPosts = $this->repo->addEditCustomPage($data, $id);
            return $blogPosts;
        } catch (\Exception $e) {
            storeException('getEditPageData',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function customPagesDelete($data, $id)
    {
        try{
            $blogPosts = $this->repo->customPagesDelete($data, $id);
            return $blogPosts;
        } catch (\Exception $e) {
            storeException('getEditPageData',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

}