<?php
use Illuminate\Support\Facades\Route;

Route::group(['group' => 'Addon', 'middleware' => 'check_addon','auth', 'admin', 'permission:Blog_News_Addon'], function () {
    Route::get('blog-news/dashboard', 'Blog\BlogDashboardController@dashboard')->name("blogDashboard");
    Route::group(['prefix' => '/blog','namespace' => 'Blog', 'group' => 'test'],function() {
        //Dashboard
        //Route::get('/', 'BlogDashboardController@dashboard')->name("blogDashboard");

        //Blog List and create
        Route::get('/list', 'AddBlogController@allBlogPage')->name("allBlogPage");
        Route::get('/blog-page/{id?}', 'AddBlogController@createBlogPage')->name("createBlogPage");
        Route::get('/post-list/{type?}/{main?}/{sub?}', 'AddBlogController@blogPostData')->name("blogPostData");
        Route::get('/get-sub-categorys-{id?}', 'AddBlogController@getSubCategorys')->name("getSubCategorys");
        Route::get('blog-translate-{id}','AddBlogController@blogTranslatePage')->name('blogTranslatePage');
        Route::get('blog-update-text-{id}-{lan_key}','AddBlogController@blogTranslateUpdatePage')->name('blogTranslateUpdatePage');
        Route::post('blog-update-text','AddBlogController@blogTranslateUpdateText')->name('blogTranslateUpdateText');

        //Blog Category
        Route::get('category','BlogCategoryController@CategoryPage')->name('CategoryPage');
        Route::get('category-submit','BlogCategoryController@CategorySubmitPage')->name('CategorySubmitPage');
        Route::get('category-edit-{id}','BlogCategoryController@CategorySubmitPage')->name('CategoryEditPage');
        Route::get('category-translate-{id}','BlogCategoryController@CategoryTranslatePage')->name('CategoryTranslatePage');
        Route::get('category-update-text-{id}-{lan_key}','BlogCategoryController@CategoryTranslateUpdatePage')->name('CategoryTranslateUpdatePage');
        Route::post('category-update-text','BlogCategoryController@CategoryTranslateUpdateText')->name('CategoryTranslateUpdateText');

        Route::get('sub-category','BlogCategoryController@SubCategoryPage')->name('SubCategoryPage');
        Route::get('sub-category-submit','BlogCategoryController@SubCategorySubmitPage')->name('SubCategorySubmitPage');
        Route::get('sub-category-edit-{id}','BlogCategoryController@SubCategorySubmitPage')->name('SubCategoryEditPage');
        Route::get('sub-category-translate-{id}','BlogCategoryController@subCategoryTranslatePage')->name('subCategoryTranslatePage');
        Route::get('sub-category-update-text-{id}-{lan_key}','BlogCategoryController@subCategoryTranslateUpdatePage')->name('subCategoryTranslateUpdatePage');
        Route::post('sub-category-update-text','BlogCategoryController@subCategoryTranslateUpdateText')->name('subCategoryTranslateUpdateText');

        // Setting category
        Route::get('settings','SettingController@settingPage')->name('BlogSettings');
        Route::get('settings-translate','SettingController@settingTranslatePage')->name('BlogSettingsTranslate');
        Route::get('settings-translate-update-{lan_key}','SettingController@settingTranslateUpdatePage')->name('BlogSettingsTranslateUpdate');
        Route::post('settings-translate-update-text','SettingController@settingTranslateUpdateText')->name('BlogSettingsTranslateUpdateText');

        // Comment
        Route::get('comments', 'CommentController@commentPage')->name('BlogComment');
        Route::get('comment-lists-{id?}', 'CommentController@commentList')->name('commentList');


        Route::group(['middleware' => 'check_demo'], function () {
            //Blog
            Route::post('/create', 'AddBlogController@createBlogProcess')->name("createBlogProcess");
            Route::post('/delete/{id}', 'AddBlogController@deleteBlogProcess')->name("deleteBlogProcess");
            //Blog Category
            Route::post('category-submit','BlogCategoryController@CategorySubmit')->name('CategorySubmit');
            //Blog Sub Category
            Route::post('sub-category-submit','BlogCategoryController@SubCategorySubmit')->name('SubCategorySubmit');
            Route::post('category-delete-{id}','BlogCategoryController@deleteCategory')->name('deleteCategory');
            // Setting category
            Route::post('settings','SettingController@settingUpdate')->name('BlogSettingUpdate');
            // Comment
            Route::get('comments-accept-{id}', 'CommentController@BlogCommentAccept')->name('BlogCommentAccept');
            Route::get('comments-delete-{id}', 'CommentController@BlogCommentDelete')->name('BlogCommentDelete');
            Route::post('comments', 'CommentController@BlogCommentEdit')->name('BlogCommentEdit');


        });
    });
    Route::group(['prefix' => '/news','namespace' => 'News', 'group' => 'test','middleware' => 'check_addon'],function() {
        //Dashboard
        //Route::get('/', 'NewsDashboardController@dashboard')->name("newsDashboard");
        //News List and create
        Route::get('/list', 'AddNewsController@allNewsPage')->name("allNewsPage");
        Route::get('/news-page/{id?}', 'AddNewsController@createNewsPage')->name("createNewsPage");
        Route::get('/post-list/{type?}/{main?}/{sub?}', 'AddNewsController@newsPostData')->name("newsPostData");
        Route::get('/get-sub-categorys-{id?}', 'AddNewsController@getNewsSubCategorys')->name("getNewsSubCategorys");
        Route::get('news-translate-{id}','AddNewsController@newsTranslatePage')->name('newsPostTranslatePage');
        Route::get('news-update-text-{id}-{lan_key}','AddNewsController@newsTranslateUpdatePage')->name('newsPostTranslateUpdatePage');
        Route::post('news-update-text','AddNewsController@newsTranslateUpdateText')->name('newsPostTranslateUpdateText');

        // //News Category
        Route::get('category','NewsCategoryController@CategoryPage')->name('newsCategoryPage');
        Route::get('category-submit','NewsCategoryController@CategorySubmitPage')->name('newsCategorySubmitPage');
        Route::get('category-edit-{id}','NewsCategoryController@CategorySubmitPage')->name('newsCategoryEditPage');
        Route::get('category-translate-{id}','NewsCategoryController@CategoryTranslatePage')->name('newsCategoryTranslatePage');
        Route::get('category-update-text-{id}-{lan_key}','NewsCategoryController@CategoryTranslateUpdatePage')->name('newsCategoryTranslateUpdatePage');
        Route::post('category-update-text','NewsCategoryController@CategoryTranslateUpdateText')->name('newsCategoryTranslateUpdateText');

        Route::get('sub-category','NewsCategoryController@SubCategoryPage')->name('newsSubCategoryPage');
        Route::get('sub-category-submit','NewsCategoryController@SubCategorySubmitPage')->name('newsSubCategorySubmitPage');
        Route::get('sub-category-edit-{id}','NewsCategoryController@SubCategorySubmitPage')->name('newsSubCategoryEditPage');

        // Setting category
        Route::get('settings','SettingController@settingPage')->name('NewsSettings');

        // Comment
        Route::get('comments', 'CommentController@commentPage')->name('NewsComment');
        Route::get('comment-lists-{id?}', 'CommentController@commentList')->name('newsCommentList');

        Route::group(['middleware' => 'check_demo'], function () {
            //News
            Route::post('/create', 'AddNewsController@createNewsProcess')->name("createNewsProcess");
            Route::post('/delete/{id}', 'AddNewsController@deleteNewsProcess')->name("deleteNewsProcess");
            //News Category
            Route::post('category-submit','NewsCategoryController@CategorySubmit')->name('newsCategorySubmit');
            Route::post('sub-category-submit','NewsCategoryController@SubCategorySubmit')->name('newsSubCategorySubmit');
            Route::post('category-delete-{id}','NewsCategoryController@deleteCategory')->name('newsDeleteCategory');
            // Setting
            Route::post('settings','SettingController@settingUpdate')->name('NewsSettingUpdate');
            // Comment
            Route::get('comments-accept-{id}', 'CommentController@NewsCommentAccept')->name('NewsCommentAccept');
            Route::get('comments-delete-{id}', 'CommentController@NewsCommentDelete')->name('NewsCommentDelete');
            Route::post('comments', 'CommentController@NewsCommentEdit')->name('NewsCommentEdit');
        });
    });


    // Route::get('news/custom-pages','CustomPagesController@getNewsCustomPages')->name("NewsCustomPages");
    // Route::get('blog/custom-pages','CustomPagesController@getBlogCustomPages')->name("BlogCustomPages");
    // Route::group(['prefix' => 'blog-news', 'group' => 'test'], function () {
    //     Route::get('custom-pages-{type}/{id?}', 'CustomPagesController@createCustomPages')->name('createCustomPage');
    //     Route::post('custom-pages', 'CustomPagesController@createCustomPagesProcess')->name('createCustomPagesProcess');
    //     Route::post('custom-pages-delete-{type}-{id}', 'CustomPagesController@customPagesDelete')->name('customPagesDelete');
    // });
});
