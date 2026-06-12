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

Route::middleware('auth:api')->get('/knowledgebase', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'knowledgebase', 'namespace' => 'Api','middleware' => ['checkApi','check_knowledgebase_module_status']], function () {
    
    Route::get('index', 'KnowledgeBaseController@index');
    Route::get('article-list-by-category', 'KnowledgeBaseController@articleListByCategory');
    Route::get('article-list-by-subcategory', 'KnowledgeBaseController@articleListBySubcategory');
    Route::get('article-details', 'KnowledgeBaseController@articleDetails');
    Route::post('article-search', 'KnowledgeBaseController@articleSearch');

    Route::group(['middleware' => ['auth:api', 'api-user']], function () {

        Route::get('support-ticket-list', 'SupportController@supportTicketList');
        Route::get('support-project-list', 'SupportController@supportProjectList');
        Route::post('support-ticket-store', 'SupportController@supportTicketStore');
        Route::get('support-ticket-conversation-details', 'SupportController@ticketConversationDetails');
        Route::post('support-ticket-conversation-send', 'SupportController@ticketConversationSend');

        //ticket note 
        Route::get('support-ticket-note-list', 'NoteController@listTicketNote');
        Route::post('support-ticket-note-create', 'NoteController@saveTicketNote');
        Route::post('support-ticket-note-delete', 'NoteController@deleteTicketNote');
    

        Route::get('support-notification-details', 'NotificationController@notificationDetails');
    });

    Route::get('site-settings-resource', 'SettingsController@index');
});