<?php

Route::group(['prefix' => '', 'namespace' => 'User', 'group' => 'knowledgebase_user','middleware' => ['off_knowledgebase','check_knowledgebase_module_status']], function () {

    Route::get('/', 'KnowledgeBaseController@index')->name('knowledgebase_user_index');
    Route::get('category-{id}', 'KnowledgeBaseController@knowledgeCategory')->name('knowledgeCategory');
    Route::get('article-list-{subcategory_unique_code}', 'KnowledgeBaseController@articleList')->name('articleList');
    Route::get('article-details-{unique_code}', 'KnowledgeBaseController@articleDetails')->name('articleDetails');
    Route::post('article-search', 'KnowledgeBaseController@articleSearch')->name('articleSearch');
    Route::post('article-search-suggestion', 'KnowledgeBaseController@articleSearchSuggestion')->name('articleSearchSuggestion');

    Route::get('support-login', 'LoginController@login')->name('support_login');
    Route::post('support-login-process', 'LoginController@loginProcess')->name('support_login_process');
    //support
    Route::group(['middleware' => 'user_auth'], function () {
        Route::get('support-logout', 'LoginController@logout')->name('support_logout');
        Route::get('support', 'SupportController@index')->name('support_dashboard');
        Route::get('support-ticket', 'SupportController@createTicket')->name('support_create_ticket');
        Route::post('support-ticket-store', 'SupportController@storeTicket')->name('support_create_ticket_store');
        Route::get('support-ticket-conversation-details-{unique_code}', 'SupportController@ticketConversationDetails')->name('support_ticket_conversation_details');
        Route::post('support-ticket-conversation-send', 'SupportController@ticketConversationSend')->name('support_ticket_conversation_send');

        //ticket note
        Route::post('support-ticket-note-create', 'NoteController@saveTicketNote')->name('user_support_ticket_note_store');
        Route::get('support-ticket-note-delete-{unique_code}', 'NoteController@deleteTicketNote')->name('user_support_ticket_note_delete');

        Route::get('support-notification-details-{unique_code}', 'NotificationController@notificationDetails')->name('support_notification_details');
    });
});
