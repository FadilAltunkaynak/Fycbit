<?php

namespace Modules\BlogNews\Http\Controllers\News;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BlogNews\Entities\NewsCategory;
use Modules\BlogNews\Http\Services\NewsService;
use Modules\BlogNews\Http\Requests\News\AddEditCatRequest;
use Modules\BlogNews\Http\Requests\News\AddEditSubCatRequest;
use App\Http\Services\AdminLangService;
use Modules\BlogNews\Http\Requests\NewsCategoryTranslationRequest;

class NewsCategoryController extends Controller
{
    private $service;
    private $languageService;

    public function __construct()
    {
        $this->service = new NewsService();
        $this->languageService = new AdminLangService;
    }
    public function CategoryPage(Request $request)
    {
        $data['title'] = __("News Category");
        if ($request->ajax()) {
            $category = NewsCategory::whereSub(STATUS_INACTIVE)->get();
            return datatables()->of($category)
                ->addColumn('title', function ($query) {
                    return $query->title;
                })
                ->addColumn('status', function ($query) {
                    return $query->status == STATUS_ACTIVE ? __('ON') : __('OFF');
                })
                ->addColumn('translation', function ($query) {
                    return translationActionButtonBlogNews('newsCategoryTranslatePage', $query->id);
                })
                ->addColumn('actions', function ($query) {
                    $action = '<div class="activity-icon"><ul>';
                    $action .= ActionButtonForList(encrypt($query->id), 'newsCategoryEditPage', 'newsDeleteCategory');
                    $action .= '</ul> </div>';
                    return $action;
                })
                ->rawColumns(['translation', 'actions'])
                ->make(true);
        }
        return view('blognews::news.category.category', $data);
    }

    public function CategorySubmitPage($id = 0)
    {
        $data = [];
        try {
            $data['title'] = __("Create News Category");
            if ($id) {
                $data['title'] = __("Update News Category");
                $data['category'] = NewsCategory::find(decrypt($id));
            }
        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
        }
        return view('blognews::news.category.addEditCat', $data);
    }

    public function CategorySubmit(AddEditCatRequest $request)
    {
        try {
            $response = $this->service->addEditCat($request);
            if ($response['success'])
                return redirect()->route('newsCategoryPage')->with('success', $response['message']);
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }
    public function SubCategoryPage(Request $request)
    {
        $data['title'] = __("News Sub Category");
        if ($request->ajax()) {
            $category = NewsCategory::whereSub(STATUS_ACTIVE)->get();
            return datatables()->of($category)
                ->addColumn('title', function ($query) {
                    return $query->title;
                })
                ->addColumn('main', function ($query) {
                    return mainNewsCategoryTitle($query->main_id);
                })
                ->addColumn('status', function ($query) {
                    return $query->status == STATUS_ACTIVE ? __('ON') : __('OFF');
                })
                ->addColumn('actions', function ($query) {
                    $action = '<div class="activity-icon"><ul>';
                    $action .= ActionButtonForList(encrypt($query->id), 'newsSubCategoryEditPage', 'newsDeleteCategory');
                    $action .= '</ul> </div>';
                    return $action;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('blognews::news.category.subcategory', $data);
    }

    public function SubCategorySubmitPage($id = 0)
    {
        try {
            $data['title'] = __("Create News Sub Category");
            if ($id) {
                $data['title'] = __("Update News Sub Category");
                $data['category'] = NewsCategory::find(decrypt($id));
            }
            $data['categorys'] = NewsCategory::where(['sub' => 0]);
            if ($data['categorys']->get()->count() <= 0)
                return redirect()->back()->with('dismiss', __('Create main category to create sub category'));
            $data['categorys'] = $data['categorys']->get();
            return view('blognews::news.category.addEditSubCat', $data);
        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
        }
    }

    public function SubCategorySubmit(AddEditSubCatRequest $request)
    {
        try {
            $response = $this->service->addEditSubCat($request);
            if ($response['success'])
                return redirect()->route('newsSubCategoryPage')->with('success', $response['message']);
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function deleteCategory($id)
    {
        try {
            $response = $this->service->deleteCategory($id);
            if ($response['success'])
                return redirect()->back()->with('success', $response['message']);
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('newsDeleteCategory', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function CategoryTranslatePage($id)
    {
        $data = [];
        try {
            $data['title'] = __("Update Languages For Category");
            $data['category'] = NewsCategory::with(['translationCategory'])->find(decrypt($id));
            $language_response = $this->languageService->activeLanguageList();
            if ($language_response['success']) {
                $data['language_list'] = $language_response['data'];
            }

        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
        }
        return view('blognews::news.category.category-translate', $data);
    }

    public function CategoryTranslateUpdatePage($id, $lang_key)
    {
        $data = [];
        try {
            $data['title'] = __("Language Update Category");
            $category_details = NewsCategory::find(decrypt($id));
            $language_details_response = $this->languageService->languageDetailsByKey($lang_key);

            if (isset($category_details) && $language_details_response['success']) {
                $data['category'] = $category_details;
                $data['language_details'] = $language_details_response['data'];

                $category_translation_response = $this->service->getCategoryDetailsTranslationByLangKeyNews($category_details->id, $data['language_details']->key);
                if ($category_translation_response['success']) {
                    $data['category_translation'] = $category_translation_response['data'];
                }


                return view('blognews::news.category.category-translate-update', $data);
            } else {
                return back()->with(['success' => 'Invalid Request']);
            }

        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
        }
        return back()->with(['success' => 'Invalid Request']);
    }

    public function CategoryTranslateUpdateText(NewsCategoryTranslationRequest $request)
    {
        $response = $this->service->updateLanguageForCategory($request);
        if ($response['success']) {
            return back()->with(['success' => $response['message']]);
        } else {
            return back()->with(['dismiss' => $response['message']]);
        }
    }
}
