<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/blognews', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'blog', 'namespace' => 'Api', 'group' => 'test'], function () {
    Route::get('get','BlogController@getBlog');
    Route::get('blog-details','BlogController@getBlogDetails');
    Route::get('category','BlogController@getCategory');

    Route::get('search','BlogController@blogSearch');

    //Comment
    Route::post('comment','CommentController@storeComment');
    Route::get('get-comment','CommentController@getComment');
});

Route::group(['prefix' => 'news', 'namespace' => 'Api', 'group' => 'test'], function () {
    Route::get('get','NewsController@getNews');
    Route::get('news-details','NewsController@getNewsDetails');
    Route::get('category','NewsController@getCategory');

    Route::get('search','NewsController@newsSearch');

    //Comment
    Route::post('comment','CommentController@storeComment');
    Route::get('get-comment','CommentController@getComment');
});

Route::group(['prefix' => 'blog-news', 'namespace' => 'Api','group' => 'test'], function () {
    Route::get('settings', 'SettingController@getSettings');
});