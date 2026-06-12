<?php

namespace Modules\BlogNews\Http\Controllers\blog;

use App\Model\LangName;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BlogNews\Entities\BlogCategory;
use Modules\BlogNews\Http\Services\BlogService;
use Modules\BlogNews\Http\Requests\Blog\AddEditCatRequest;
use Modules\BlogNews\Http\Requests\Blog\AddEditSubCatRequest;
use App\Http\Services\AdminLangService;
use Modules\BlogNews\Http\Requests\BlogCategoryTranslationRequest;

class BlogCategoryController extends Controller
{
    private $service;
    private $languageService;
    public function __construct()
    {
        $this->service = new BlogService();
        $this->languageService = new AdminLangService;
    }
    public function CategoryPage(Request $request)
    {
        $data['title'] = __("Blog Category");
        if ($request->ajax()) {
            $category = BlogCategory::whereSub(STATUS_INACTIVE)->get();
            return datatables()->of($category)
                ->addColumn('title', function ($query) {
                    return $query->title;
                })
                ->addColumn('status', function ($query) {
                    return $query->status == STATUS_ACTIVE ? __('ON') : __('OFF');
                })
                ->addColumn('translation', function ($query) {

                    return translationActionButtonBlogNews('CategoryTranslatePage', $query->id);

                })
                ->addColumn('actions', function ($query) {
                    $action = '<div class="activity-icon"><ul>';
                    $action .= ActionButtonForList($query->id, 'CategoryEditPage', 'deleteCategory');
                    $action .= '</ul> </div>';
                    return $action;
                })
                ->rawColumns(['actions', 'translation'])
                ->make(true);
        }
        return view('blognews::blog.category.category', $data);
    }

    public function CategorySubmitPage($id = 0)
    {
        $data = [];
        try {
            $data['title'] = __("Create Blog Category");
            if ($id) {
                $data['title'] = __("Update Blog Category");
                $data['category'] = BlogCategory::find($id);
            }
        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
        }
        return view('blognews::blog.category.addEditCat', $data);
    }

    public function CategorySubmit(AddEditCatRequest $request)
    {
        try {
            $response = $this->service->addEditCat($request);
            if ($response['success'])
                return redirect()->route('CategoryPage')->with('success', $response['message']);
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }
    public function SubCategoryPage(Request $request)
    {
        $data['title'] = __("Blog Sub Category");
        if ($request->ajax()) {
            $category = BlogCategory::whereSub(STATUS_ACTIVE)->get();
            return datatables()->of($category)
                ->addColumn('title', function ($query) {
                    return $query->title;
                })
                ->addColumn('main', function ($query) {
                    return mainCategoryTitle($query->main_id);
                })
                ->addColumn('status', function ($query) {
                    return $query->status == STATUS_ACTIVE ? __('ON') : __('OFF');
                })
                ->addColumn('translation', function ($query) {

                    return translationActionButtonBlogNews('subCategoryTranslatePage', $query->id);

                })
                ->addColumn('actions', function ($query) {
                    $action = '<div class="activity-icon"><ul>';
                    $action .= ActionButtonForList($query->id, 'SubCategoryEditPage', 'deleteCategory');
                    $action .= '</ul> </div>';
                    return $action;
                })
                ->rawColumns(['translation', 'actions'])
                ->make(true);
        }
        return view('blognews::blog.category.subcategory', $data);
    }

    public function SubCategorySubmitPage($id = 0)
    {
        $data = [];
        try {
            $data['title'] = __("Create Blog Sub Category");
            if ($id) {
                $data['title'] = __("Update Blog Sub Category");
                $data['category'] = BlogCategory::find($id);
            }
            $data['categorys'] = BlogCategory::where(['sub' => 0]);
            if ($data['categorys']->get()->count() <= 0)
                return redirect()->back()->with('dismiss', __('Create main category to create sub category'));
            $data['categorys'] = $data['categorys']->get();

        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
        }
        return view('blognews::blog.category.addEditSubCat', $data);
    }

    public function SubCategorySubmit(AddEditSubCatRequest $request)
    {
        try {
            $response = $this->service->addEditSubCat($request);
            if ($response['success'])
                return redirect()->route('SubCategoryPage')->with('success', $response['message']);
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
            storeException('deleteCategory', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function CategoryTranslatePage($id)
    {
        $data = [];
        try {
            $data['title'] = __("Update Languages For Category");
            $data['category'] = BlogCategory::with(['translationCategory'])->find(decrypt($id));
            $language_response = $this->languageService->activeLanguageList();
            if ($language_response['success']) {
                $data['language_list'] = $language_response['data'];
            }

        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
        }
        return view('blognews::blog.category.category-translate', $data);
    }

    public function CategoryTranslateUpdatePage($id, $lang_key)
    {
        $data = [];
        try {
            $data['title'] = __("Language Update Category");
            $category_details = BlogCategory::find(decrypt($id));
            $language_details_response = $this->languageService->languageDetailsByKey($lang_key);

            if (isset($category_details) && $language_details_response['success']) {
                $data['category'] = $category_details;
                $data['language_details'] = $language_details_response['data'];

                $category_translation_response = $this->service->getCategoryDetailsTranslationByLangKey($category_details->id, $data['language_details']->key);
                if ($category_translation_response['success']) {
                    $data['category_translation'] = $category_translation_response['data'];
                }


                return view('blognews::blog.category.category-translate-update', $data);
            } else {
                return back()->with(['success' => 'Invalid Request']);
            }

        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
        }
        return back()->with(['success' => 'Invalid Request']);
    }

    public function CategoryTranslateUpdateText(BlogCategoryTranslationRequest $request)
    {
        $response = $this->service->updateLanguageForCategory($request);
        if ($response['success']) {
            return back()->with(['success' => $response['message']]);
        } else {
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function subCategoryTranslatePage($id)
    {
        $data = [];
        try {
            $data['title'] = __("Update Languages For Sub Category");
            $data['category'] = BlogCategory::with(['translationCategory'])->find(decrypt($id));
            $language_response = $this->languageService->activeLanguageList();
            if ($language_response['success']) {
                $data['language_list'] = $language_response['data'];
            }

        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
        }
        return view('blognews::blog.category.sub-category-translate', $data);
    }

    public function subCategoryTranslateUpdatePage($id, $lang_key)
    {
        $data = [];
        try {
            $data['title'] = __("Language Update Sub Category");
            $category_details = BlogCategory::find(decrypt($id));
            $language_details_response = $this->languageService->languageDetailsByKey($lang_key);

            if (isset($category_details) && $language_details_response['success']) {
                $data['category'] = $category_details;
                $data['language_details'] = $language_details_response['data'];

                $category_translation_response = $this->service->getCategoryDetailsTranslationByLangKey($category_details->id, $data['language_details']->key);
                if ($category_translation_response['success']) {
                    $data['category_translation'] = $category_translation_response['data'];
                }


                return view('blognews::blog.category.sub-category-translate-update', $data);
            } else {
                return back()->with(['success' => 'Invalid Request']);
            }

        } catch (\Exception $e) {
            storeException('CategoryPage', $e->getMessage());
        }
        return back()->with(['success' => 'Invalid Request']);
    }

    public function subCategoryTranslateUpdateText(BlogCategoryTranslationRequest $request)
    {
        $response = $this->service->updateLanguageForSubCategory($request);
        if ($response['success']) {
            return back()->with(['success' => $response['message']]);
        } else {
            return back()->with(['dismiss' => $response['message']]);
        }
    }
}
