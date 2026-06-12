<?php

namespace Modules\BlogNews\Repository;

use Illuminate\Support\Facades\DB;
use Modules\BlogNews\Entities\BlogComment;
use Modules\BlogNews\Entities\BlogPost;
use Illuminate\Support\Facades\RateLimiter;
use Modules\BlogNews\Entities\BlogCategory;
use Modules\BlogNews\Entities\BlogCategoryTranslation;
use Modules\BlogNews\Entities\BlogPostTranslation;
use Modules\BlogNews\Entities\BlogViewsReport;
use stdClass;

class BlogRepository
{

    public function blogSave($data, $slug)
    {
        try {
            $responseErr = responseData(false, __('Blog post not saved successfully'));
            $responseSuc = responseData(true, __('Blog post saved successfully'));
            if ($slug) {
                $responseErr = responseData(false, __('Blog post not updated successfully'));
                $responseSuc = responseData(true, __('Blog post updated successfully'));
            }
            DB::beginTransaction();
            $save = BlogPost::updateOrCreate(['slug' => $slug], $data);
            DB::commit();
            if ($save) {
                return $responseSuc;
            } else {
                return $responseErr;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('blogSave repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getBlogPosts($type, $main, $sub, $extra)
    {
        try {
            $posts = null;
            $lang_key = isset($extra['lang_key']) ? $extra['lang_key'] : 'en';
            if ($type == TYPE_MAIN_CATEGORY) {
                if ($main) {
                    $posts = BlogPost::with([
                        'translationBlogPost' => function ($query) use ($lang_key) {

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
                    $posts = BlogPost::with([
                        'translationBlogPost' => function ($query) use ($lang_key) {

                            $query->where('lang_key', $lang_key);

                        }
                    ]);
            }
            if ($type == TYPE_SUB_CATEGORY) {
                if ($sub) {
                    $posts = BlogPost::with([
                        'translationBlogPost' => function ($query) use ($lang_key) {

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
                    $posts = BlogPost::with([
                        'translationBlogPost' => function ($query) use ($lang_key) {

                            $query->where('lang_key', $lang_key);

                        }
                    ]);
            }
            if ($posts == null) {
                $posts = BlogPost::with([
                    'translationBlogPost' => function ($query) use ($lang_key) {

                        $query->where('lang_key', $lang_key);

                    }
                ])->where(['status' => STATUS_ACTIVE]);
                if (isset($extra['type']) && $extra['type'] == TYPE_BLOG_POPULER) {
                    $posts = $posts->wherePublish(STATUS_ACTIVE)
                        ->where('publish_at', '<>', NULL)
                        ->where('publish_at', '<=', date("Y.m.d H:i:s"))
                        ->orderBy('views', 'DESC');
                } else if (isset($extra['type']) && $extra['type'] == TYPE_BLOG_FEATURED) {
                    $posts = $posts->where('is_fetured', STATUS_ACTIVE)
                        ->wherePublish(STATUS_ACTIVE)
                        ->where('publish_at', '<>', NULL)
                        ->where('publish_at', '<=', date("Y.m.d H:i:s"))
                        ->orderBy('views', 'DESC');
                } else {
                    $posts = $posts->wherePublish(STATUS_ACTIVE)
                        ->where('publish_at', '<>', NULL)
                        ->where('publish_at', '<=', date("Y.m.d H:i:s"))
                        ->orderBy('created_at', 'DESC');
                }
                // return $posts->get();
            }
            // $selectedColumns = ['translation_category','id','thumbnail','body', 'title', 'slug','status', 'publish','comment_allow'];
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
                    if ($post->translationBlogPost->count() > 0) {
                        $post->title = $post->translationBlogPost[0]->title;
                        $post->description = substr(preg_replace("%([\\n\\r])%im", '', preg_replace("%(<.+?>)%im", '', $post->translationBlogPost[0]->body)), 0, 250);
                    } else {

                        $post->description = substr(preg_replace("%([\\n\\r])%im", '', preg_replace("%(<.+?>)%im", '', $post->body)), 0, 250);
                    }
                } else {
                    $post->description = substr(preg_replace("%([\\n\\r])%im", '', preg_replace("%(<.+?>)%im", '', $post->body)), 0, 250);
                }
                $post->thumbnail = $post->thumbnail ? asset(BLOG_THUMBNAIL_PATH . $post->thumbnail) : null;
                $post->post_id = $post->slug;
                $post->body = '';
            });
            return responseData(true, __("Blog posts get successfully"), $posts);
        } catch (\Exception $e) {
            storeException('getBlogPosts repo', $e->getMessage());
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
            $posts = BlogCategory::updateOrCreate(['id' => $id], $data);
            if ($posts)
                return $responseSuc;
            return $responseErr;
        } catch (\Exception $e) {
            storeException('addEditCat repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    private function viewsReport()
    {
        try {
            $day = date("l");
            $repost = BlogViewsReport::firstOrCreate(['day' => $day], ['date' => date("Y-m-d H:i:s")]);
            if ($repost) {
                $repost->increment('count', 1);
                $repost->save();
            }
        } catch (\Exception $e) {
            storeBotException('viewsReport', $e->getMessage());
        }
    }

    public function getBlogDetails($slug, $lang_key = null)
    {
        try {
            $reletedDate = new stdClass;
            $responseErr = responseData(false, __('Blog not found'));
            $responseSuc = responseData(true, __('Blog get Successful'));
            $posts = BlogPost::with([
                'translationBlogPost' => function ($query) use ($lang_key) {

                    $query->where('lang_key', $lang_key);

                }
            ])->where('slug', $slug)->first();
            if ($posts) {
                $ip = request()->ip();
                $executed = RateLimiter::attempt("blog-set-counter:$slug-$ip", 1, function () {/**/}, 3600);
                if ($executed) {
                    $this->viewsReport();
                    $post = $posts;
                    $post->increment('views', 1);
                    $post->save();
                }
                if ($posts->translationBlogPost->count() > 0) {
                    $posts->title = $posts->translationBlogPost[0]->title;
                    $posts->body = $posts->translationBlogPost[0]->body;

                }
                $posts->post_id = $posts->slug;
                $posts->thumbnail = asset(BLOG_THUMBNAIL_PATH . $posts->thumbnail);
                $releted = $this->getBlogPosts(TYPE_SUB_CATEGORY, $posts->category, $posts->sub_category, ['api' => true, 'lang_key' => $lang_key]);
                $commetn = (new CommentRepository())->getComment(BlogComment::class, ['post_id' => $slug, 'limit' => 0]);
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
            storeException('$getBlogDetails repo', $e->getMessage());
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
            $posts = BlogCategory::updateOrCreate(['id' => $id], $data);
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
            $data = BlogCategory::where(['sub' => STATUS_INACTIVE, 'status' => STATUS_ACTIVE])->get();
            return responseData(true, __('Category get successfully'), $data);
        } catch (\Exception $e) {
            storeException('getMainCategory repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function blogSearch($request, $extra = [])
    {
        try {
            $query = $request->value ?? '';
            $lang_key = $request->header('lang') ?? 'en';
            $data = BlogPost::with([
                'translationBlogPost' => function ($query) use ($lang_key) {

                    $query->where('lang_key', $lang_key);

                }
            ])->where('title', 'like', "%$query%")
                ->orWhere('body', 'like', "%$query%");


            if (isset($request->limit))
                $data = $data->limit($request->limit)->get();
            else
                $data = $data->get();

            // if not found in then search totranslation model
            if ($data->count() == 0) {
                $blog_post_ids = BlogPostTranslation::where('lang_key', $lang_key)->where('title', 'like', "%$query%")
                    ->orWhere('body', 'like', "%$query%")->pluck('blog_post_id')->toArray();

                $data = BlogPost::with([
                    'translationBlogPost' => function ($query) use ($lang_key) {

                        $query->where('lang_key', $lang_key);

                    }
                ])->whereIn('id', $blog_post_ids);

                if (isset($request->limit))
                    $data = $data->limit($request->limit)->get();
                else
                    $data = $data->get();

            }

            $data->map(function ($post) use ($extra) {
                $post->post_id = $post->slug;
                if (isset($extra['api']) && $extra['api']) {
                    if ($post->translationBlogPost->count() > 0) {
                        $post->title = $post->translationBlogPost[0]->title;
                        $post->description = $post->translationBlogPost[0]->body;
                    }
                }

            });

            return responseData(true, __('Search reasult get successfully'), $data);
        } catch (\Exception $e) {
            storeException('blogSearch repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function findBlog($slug)
    {
        try {
            $data = BlogPost::where('slug', $slug)->first();
            return responseData(true, __('Blog get successfully'), $data);
        } catch (\Exception $e) {
            storeException('findBlog repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getBlogData($slug)
    {
        try {
            $data = BlogPost::where('slug', $slug)->first();
            $data->thumbnail = asset(BLOG_THUMBNAIL_PATH . $data->thumbnail ?? '');
            return responseData(true, __('Blog get successfully'), $data);
        } catch (\Exception $e) {
            storeException('getBlogData repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getSubCategory($subCat)
    {
        try {
            if (isset($subCat->category)) {
                $data = BlogCategory::where(['main_id' => $subCat->category, 'status' => STATUS_ACTIVE])->get();
                return responseData(true, __('Blog get successfully'), $data);
            }
            return responseData(false, __('Blog get failed'));
        } catch (\Exception $e) {
            storeException('getSubCategory repo', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    public function updateLanguageForCategory($request)
    {
        try {

            $category_translation = BlogCategoryTranslation::where('blog_category_id', $request->category_id)
                ->where('lang_key', $request->lang_key)->first();

            if (!isset($category_translation)) {
                $category_translation = new BlogCategoryTranslation;
            }
            $category_translation->blog_category_id = $request->category_id;
            $category_translation->lang_key = $request->lang_key;
            $category_translation->title = $request->title;
            $category_translation->save();


            return responseData(true, __('Blog Category Language Text is Updated'));
        } catch (\Exception $e) {
            storeException('updateLanguageForCategory', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function updateLanguageForSubCategory($request)
    {
        try {

            $category_translation = BlogCategoryTranslation::where('blog_category_id', $request->category_id)
                ->where('lang_key', $request->lang_key)->first();

            if (!isset($category_translation)) {
                $category_translation = new BlogCategoryTranslation;
            }
            $category_translation->blog_category_id = $request->category_id;
            $category_translation->lang_key = $request->lang_key;
            $category_translation->title = $request->title;
            $category_translation->save();


            return responseData(true, __('Blog Sub Category Language Text is Updated'));
        } catch (\Exception $e) {
            storeException('updateLanguageForSubCategory', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getCategoryDetailsTranslationByLangKey($category_id, $lang_key)
    {
        $category_details = BlogCategoryTranslation::where('blog_category_id', $category_id)
            ->where('lang_key', $lang_key)->first();

        $response = ['success' => true, 'message' => __('Category details for Language'), 'data' => $category_details];


        return $response;
    }

    public function getBlogPostDetails($id)
    {
        $blog_post_details = BlogPost::find($id);
        $response = ['success' => true, 'message' => __('Blog post details'), 'data' => $blog_post_details];
        return $response;
    }

    public function getBlogDetailsTranslationByLangKey($blog_post_id, $lang_key)
    {
        $blog_post_details = BlogPostTranslation::where('blog_post_id', $blog_post_id)
            ->where('lang_key', $lang_key)->first();

        $response = ['success' => true, 'message' => __('Blog Post details for Language'), 'data' => $blog_post_details];


        return $response;
    }

    public function updateLanguageForBlogPost($request)
    {
        try {

            $blog_translation = BlogPostTranslation::where('blog_post_id', $request->blog_post_id)
                ->where('lang_key', $request->lang_key)->first();

            if (!isset($blog_translation)) {
                $blog_translation = new BlogPostTranslation;
            }
            $blog_translation->blog_post_id = $request->blog_post_id;
            $blog_translation->lang_key = $request->lang_key;
            $blog_translation->title = $request->title;
            $blog_translation->body = $request->body;
            $blog_translation->save();


            return responseData(true, __('Blog Post Language Text is Updated'));
        } catch (\Exception $e) {
            storeException('updateLanguageForBlogPost', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}