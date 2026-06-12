<?php

namespace Modules\BlogNews\Repository;

use stdClass;
use Illuminate\Support\Facades\DB;
use Modules\BlogNews\Entities\NewsPost;
use Modules\BlogNews\Entities\NewsComment;
use Illuminate\Support\Facades\RateLimiter;
use Modules\BlogNews\Entities\NewsCategory;
use Modules\BlogNews\Entities\NewsCategoryTranslation;
use Modules\BlogNews\Entities\NewsPostTranslation;
use Modules\BlogNews\Entities\NewsViewsReport;
use Modules\BlogNews\Repository\CommentRepository;
use Yajra\DataTables\Html\Options\Languages\Paginate;

class NewsRepository
{

    public function newsSave($data, $slug)
    {
        try {
            $responseErr = responseData(false, __('News post not saved successfully'));
            $responseSuc = responseData(true, __('News post saved successfully'));
            if ($slug) {
                $responseErr = responseData(false, __('News post not updated successfully'));
                $responseSuc = responseData(true, __('News post updated successfully'));
            }
            DB::beginTransaction();
            $save = NewsPost::updateOrCreate(['slug' => $slug], $data);
            DB::commit();
            if ($save) {
                return $responseSuc;
            } else {
                return $responseErr;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('newsSave repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getNewsPosts($type, $main, $sub, $extra)
    {
        try {
            $posts = null;
            $lang_key = isset($extra['lang_key']) ? $extra['lang_key'] : 'en';
            if ($type == TYPE_MAIN_CATEGORY) {
                if ($main) {
                    $posts = NewsPost::with([
                        'translationNewsPost' => function ($query) use ($lang_key) {

                            $query->where('lang_key', $lang_key);

                        }
                    ])->where('category', $main);
                    if (isset($extra['api']) && $extra['api'] == true) {
                        $posts = $posts->whereStatus(STATUS_ACTIVE)
                            ->wherePublish(STATUS_ACTIVE)
                            ->where('publish_at', '<>', NULL)
                            ->where('publish_at', '<=', date("Y.m.d H:i:s"));
                    }
                } else
                    $posts = NewsPost::with([
                        'translationNewsPost' => function ($query) use ($lang_key) {

                            $query->where('lang_key', $lang_key);

                        }
                    ]);
            }
            if ($type == TYPE_SUB_CATEGORY) {
                if ($sub) {
                    $posts = NewsPost::with([
                        'translationNewsPost' => function ($query) use ($lang_key) {

                            $query->where('lang_key', $lang_key);

                        }
                    ])->where(['category' => $main, 'sub_category' => $sub]);
                    if (isset($extra['api']) && $extra['api'] == true) {
                        $posts = $posts->whereStatus(STATUS_ACTIVE)
                            ->wherePublish(STATUS_ACTIVE)
                            ->where('publish_at', '<>', NULL)
                            ->where('publish_at', '<=', date("Y.m.d H:i:s"));
                    }
                } else
                    $posts = NewsPost::with([
                        'translationNewsPost' => function ($query) use ($lang_key) {

                            $query->where('lang_key', $lang_key);

                        }
                    ]);
            }

            if ($posts == null) {
                $posts = NewsPost::with([
                    'translationNewsPost' => function ($query) use ($lang_key) {

                        $query->where('lang_key', $lang_key);

                    }
                ])->where(['status' => STATUS_ACTIVE]);
                if (isset($extra['type']) && $extra['type'] == TYPE_NEWS_POPULER) {
                    $posts = $posts->wherePublish(STATUS_ACTIVE)
                        ->where('publish_at', '<>', NULL)
                        ->where('publish_at', '<=', date("Y.m.d H:i:s"))
                        ->orderBy('views', 'DESC');
                } else {
                    $posts = $posts->wherePublish(STATUS_ACTIVE)
                        ->where('publish_at', '<>', NULL)
                        ->where('publish_at', '<=', date("Y.m.d H:i:s"))
                        ->orderBy('created_at', 'DESC');
                }
            }
            // $selectedColumns = ['id','thumbnail', 'title','slug','body', 'status', 'created_at', 'publish','comment_allow'];
            $posts = (is_string($posts) ?
                ((isset($extra['limit']) && $extra['limit'] > 0) ? $posts::paginate($extra['limit']) :
                    (
                        (isset($extra['api']) && $extra['api']) ?
                        $posts::select()->paginate(5) :
                        $posts::get()
                    )
                ) : (
                    (isset($extra['limit']) && $extra['limit'] > 0) ? $posts->paginate($extra['limit']) :
                    (
                        (isset($extra['api']) && $extra['api']) ?
                        $posts->select()->paginate(5) :
                        $posts->get()
                    )
                )
            );

            $posts->map(function ($post) use ($extra) {
                if (isset($extra['api']) && $extra['api']) {
                    if ($post->translationNewsPost->count() > 0) {
                        $post->title = $post->translationNewsPost[0]->title;
                        $post->description = substr(preg_replace("%([\\n\\r])%im", '', preg_replace("%(<.+?>)%im", '', $post->translationNewsPost[0]->body)), 0, 250);
                    } else {

                        $post->description = substr(preg_replace("%([\\n\\r])%im", '', preg_replace("%(<.+?>)%im", '', $post->body)), 0, 250);
                    }
                } else {
                    $post->description = substr(preg_replace("%([\\n\\r])%im", '', preg_replace("%(<.+?>)%im", '', $post->body)), 0, 250);
                }

                $post->thumbnail = asset(NEWS_THUMBNAIL_PATH . $post->thumbnail);
                $post->post_id = $post->slug;
                $post->body = '';
            });
            return responseData(true, __("News posts get successfully"), $posts);
        } catch (\Exception $e) {
            storeException('getNewsPosts repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function addEditCat($data, $id)
    {
        try {
            $responseErr = responseData(false, __('Category created failed'));
            $responseSuc = responseData(true, __('Category created Successful'));
            if ($id) {
                $responseErr = responseData(false, __('Category update failed'));
                $responseSuc = responseData(true, __('Category update Successful'));
            }
            $posts = NewsCategory::updateOrCreate(['id' => $id], $data);
            if ($posts)
                return $responseSuc;
            return $responseErr;
        } catch (\Exception $e) {
            storeException('addEditCat repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function addEditSubCat($data, $id)
    {
        try {
            $responseErr = responseData(false, __('Sub category created failed'));
            $responseSuc = responseData(true, __('Sub category created Successful'));
            if ($id) {
                $responseErr = responseData(false, __('Sub category update failed'));
                $responseSuc = responseData(true, __('Sub category update Successful'));
            }
            $posts = NewsCategory::updateOrCreate(['id' => $id], $data);
            if ($posts)
                return $responseSuc;
            return $responseErr;
        } catch (\Exception $e) {
            storeException('addEditSubCat repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getMainCategory()
    {
        try {
            $data = NewsCategory::where(['sub' => STATUS_INACTIVE, 'status' => STATUS_ACTIVE])->get();
            return responseData(true, __('Category get successfully'), $data);
        } catch (\Exception $e) {
            storeException('getMainCategory repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function findNews($slug)
    {
        try {
            $data = NewsPost::where('slug', $slug)->first();
            return responseData(true, __('News get successfully'), $data);
        } catch (\Exception $e) {
            storeException('findNews repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getNewsData($slug)
    {
        try {
            $data = NewsPost::where('slug', $slug)->first();
            $data->thumbnail = asset(NEWS_THUMBNAIL_PATH . $data->thumbnail ?? '');
            return responseData(true, __('News get successfully'), $data);
        } catch (\Exception $e) {
            storeException('getNewsData repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getSubCategory($subCat)
    {
        try {
            if (isset($subCat->category)) {
                $data = NewsCategory::where(['main_id' => $subCat->category, 'status' => STATUS_ACTIVE])->get();
                return responseData(true, __('News get successfully'), $data);
            }
            return responseData(false, __('News get failed'));
        } catch (\Exception $e) {
            storeException('getSubCategory repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    private function viewsReport()
    {
        try {
            $day = date("l");
            $repost = NewsViewsReport::firstOrCreate(['day' => $day], ['date' => date("Y-m-d H:i:s")]);
            if ($repost) {
                $repost->increment('count', 1);
                $repost->save();
            }
        } catch (\Exception $e) {
            storeBotException('viewsReport news repo', $e->getMessage());
        }
    }

    public function newsSearch($request)
    {
        try {
            $query = $request->value ?? '';
            $data = NewsPost::where('title', 'like', "%$query%")
                ->orWhere('body', 'like', "%$query%");

            if (isset($request->limit))
                $data = $data->limit($request->limit)->get();
            else
                $data = $data->get();

            $data->map(function ($post) {
                $post->post_id = $post->slug;
                $post->id = 0;
            });
            return responseData(true, __('Search reasult get successfully'), $data);
        } catch (\Exception $e) {
            storeException('newsSearch repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getNewsDetails($slug, $lang_key = null)
    {
        try {
            $reletedDate = new stdClass;
            $responseErr = responseData(false, __('News not found'));
            $responseSuc = responseData(true, __('News get Successful'));
            $posts = NewsPost::with([
                'translationNewsPost' => function ($query) use ($lang_key) {

                    $query->where('lang_key', $lang_key);

                }
            ])->where('slug', $slug)->first();
            if ($posts) {
                $ip = request()->ip();
                $executed = RateLimiter::attempt("news-set-counter:$slug-$ip", 1, function () {/**/}, 3600);
                if ($executed) {
                    $this->viewsReport();
                    $post = $posts;
                    $post->increment('views', 1);
                    $post->save();
                }

                if ($posts->translationNewsPost->count() > 0) {
                    $posts->title = $posts->translationNewsPost[0]->title;
                    $posts->body = $posts->translationNewsPost[0]->body;

                }

                $posts->post_id = encrypt($posts->id);
                $posts->thumbnail = asset(NEWS_THUMBNAIL_PATH . $posts->thumbnail);
                $posts->id = 0;
                $releted = $this->getNewsPosts(TYPE_SUB_CATEGORY, $posts->category, $posts->sub_category, ['api' => true, 'lang_key' => $lang_key]);
                $commetn = (new CommentRepository())->getComment(NewsComment::class, ['post_id' => $slug, 'limit' => 0]);
                if ($releted['success']) {
                    $reletedDate->related = $releted['data'];
                    $reletedDate->details = $posts;
                }
                if ($commetn['success']) {
                    $reletedDate->comments = $commetn['data'];
                }
                $responseSuc['data'] = $reletedDate;
                return $responseSuc;
            }
            return $responseErr;
        } catch (\Exception $e) {
            storeException('$getNewsDetails repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getCategoryDetailsTranslationByLangKeyNews($category_id, $lang_key)
    {
        $category_details = NewsCategoryTranslation::where('news_category_id', $category_id)
            ->where('lang_key', $lang_key)->first();

        $response = ['success' => true, 'message' => __('Category details for Language'), 'data' => $category_details];


        return $response;
    }

    public function updateLanguageForCategory($request)
    {
        try {

            $category_translation = NewsCategoryTranslation::where('news_category_id', $request->category_id)
                ->where('lang_key', $request->lang_key)->first();

            if (!isset($category_translation)) {
                $category_translation = new NewsCategoryTranslation;
            }
            $category_translation->news_category_id = $request->category_id;
            $category_translation->lang_key = $request->lang_key;
            $category_translation->title = $request->title;
            $category_translation->save();


            return responseData(true, __('News Category Language Text is Updated'));
        } catch (\Exception $e) {
            storeException('updateLanguageForSubCategory', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getNewsDetailsTranslationByLangKey($news_post_id, $lang_key)
    {
        $news_post_details = NewsPostTranslation::where('news_post_id', $news_post_id)
            ->where('lang_key', $lang_key)->first();

        $response = ['success' => true, 'message' => __('News Post details for Language'), 'data' => $news_post_details];


        return $response;
    }

    public function updateLanguageForNewsPost($request)
    {
        try {

            $blog_translation = NewsPostTranslation::where('news_post_id', $request->news_post_id)
                ->where('lang_key', $request->lang_key)->first();

            if (!isset($blog_translation)) {
                $blog_translation = new NewsPostTranslation;
            }
            $blog_translation->news_post_id = $request->news_post_id;
            $blog_translation->lang_key = $request->lang_key;
            $blog_translation->title = $request->title;
            $blog_translation->body = $request->body;
            $blog_translation->save();


            return responseData(true, __('News Post Language Text is Updated'));
        } catch (\Exception $e) {
            storeException('updateLanguageForNewsPost', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}