<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['prefix'=>'knowledgebase/admin','namespace' => 'Admin','group'=>'knowledgebase_dashboard','middleware' => ['check_knowledgebase_module_status','auth', 'admin', 'default_lang','permission']],function() {
    Route::get('/', 'KnowledgeBaseController@index');

    Route::get('dashboard', 'DashboardController@index')->name('knowledgebase_dashboard');

    //category
    Route::get('category-list', 'CategoryController@list')->name('knowledgebase_categoryList');
    Route::get('category-add', 'CategoryController@add')->name('knowledgebase_categoryAdd');
    Route::get('category-edit-{unique_code}', 'CategoryController@edit')->name('knowledgebase_categoryEdit');
    
    Route::group(['middleware' => 'check_demo'], function () {
        Route::post('category-save', 'CategoryController@save')->name('knowledgebase_categorySave');
        Route::get('category-delete-{unique_code}', 'CategoryController@delete')->name('knowledgebase_categoryDelete');
    });

    //sub category
    Route::get('sub-category-list', 'SubCategoryController@list')->name('knowledgebase_subCategoryList');
    Route::get('sub-category-add', 'SubCategoryController@add')->name('knowledgebase_subCategoryAdd');
    Route::get('sub-category-{unique_code}', 'SubCategoryController@edit')->name('knowledgebase_subCategoryEdit');
    
    Route::group(['middleware' => 'check_demo'], function () {
        Route::post('sub-category-save', 'SubCategoryController@save')->name('knowledgebase_subCategorySave');
        Route::get('subcategory-delete-{unique_code}', 'SubCategoryController@deleteSubCategory')->name('knowledgebase_subCategoryDelete');
    });

    //article
    Route::get('article-list', 'ArticleController@list')->name('knowledgebase_articleList');
    Route::get('article-add', 'ArticleController@add')->name('knowledgebase_articleAdd');
    Route::get('article-edit-{unique_code}', 'ArticleController@edit')->name('knowledgebase_articleEdit');
    Route::post('set-sub-category-article', 'ArticleController@setSubCategory')->name('knowledgebase_setSubCategory');

    Route::group(['middleware' => 'check_demo'], function () {
        Route::post('article-save', 'ArticleController@save')->name('knowledgebase_articleSave');
        Route::get('article-delete-{unique_code}', 'ArticleController@delete')->name('knowledgebase_articleDelete');
    });

    //article-section
    Route::get('article-section-list-{article_unique_code}', 'ArticleSectionController@list')->name('knowledgebase_articleSectionList');
    Route::get('article-section-add-{article_unique_code}', 'ArticleSectionController@add')->name('knowledgebase_articleSectionAdd');
    Route::get('article-section-edit-{article_unique_code}-{section_unique_code}', 'ArticleSectionController@edit')->name('knowledgebase_articleSectionEdit');
    
    Route::group(['middleware' => 'check_demo'], function () {
        Route::post('article-section-save', 'ArticleSectionController@save')->name('knowledgebase_articleSectionSave');        
        Route::get('article-section-delete-{article_unique_code}-{section_unique_code}', 'ArticleSectionController@delete')->name('knowledgebase_articleSectionDelete');
    });
        
    Route::group(['namespace' => 'Support'], function () {
        //support category
        Route::get('support-category-list', 'CategoryController@list')->name('support_category_list');
        Route::get('support-category-add', 'CategoryController@add')->name('support_category_add');
        Route::get('support-category-edit-{unique_code}', 'CategoryController@edit')->name('support_category_edit');
        
        Route::group(['middleware' => 'check_demo'], function () {
            Route::post('support-category-save', 'CategoryController@save')->name('support_category_save');       
            Route::get('support-category-delete-{unique_code}', 'CategoryController@delete')->name('support_category_delete');
        });

        //project
        Route::get('support-project-list', 'ProjectController@list')->name('support_project_list');
        Route::get('support-project-add', 'ProjectController@add')->name('support_project_add');
        Route::get('support-project-edit-{unique_code}', 'ProjectController@edit')->name('support_project_edit');
        
        Route::group(['middleware' => 'check_demo'], function () {
            Route::post('support-project-save', 'ProjectController@save')->name('support_project_save');
            Route::get('support-project-delete-{unique_code}', 'ProjectController@delete')->name('support_project_delete');
        });

        //Ticket 
        Route::get('support-ticket-list', 'SupportController@list')->name('support_ticket_list');
        Route::get('support-ticket-conversation-{unique_code}', 'SupportController@ticket_conversation')->name('support_agent_ticket_conversation');
        Route::post('support-ticket-conversation-send', 'SupportController@ticketConversationSend')->name('support_agent_ticket_conversation_send');
        Route::post('support-ticket-status-change', 'SupportController@ticketStatusChange')->name('support_agent_ticket_status_change');
        Route::post('support-ticket-category-change', 'SupportController@ticketCategoryChange')->name('support_agent_ticket_category_change');
        Route::post('support-ticket-project-change', 'SupportController@ticketProjectChange')->name('support_agent_ticket_project_change');
        Route::post('support-ticket-agent-change', 'SupportController@ticketAgentChange')->name('support_agent_ticket_agent_change');
        
        //ticket note
        Route::post('support-ticket-note-create', 'NoteController@saveTicketNote')->name('admin_support_ticket_note_store');
        Route::get('support-ticket-note-delete-{unique_code}', 'NoteController@deleteTicketNote')->name('admin_support_ticket_note_delete');
    });

    //notification
    Route::get('support-notification-details-{unique_code}', 'NotificationController@notificationDetails')->name('support_agent_notification_details');

    //site settings 
    Route::get('knowledgebase-site-settings', 'DashboardController@siteSettings')->name('knowledgebase_site_settings');
    Route::get('knowledgebase-site-text-settings', 'DashboardController@siteTextSettings')->name('knowledgebase_site_text_settings');
    Route::get('knowledgebase-site-text-reset', 'DashboardController@siteTextReset')->name('knowledgebase_site_text_reset');
    Route::get('knowledgebase-site-icon-list', 'DashboardController@siteIconList')->name('knowledgebase_site_icon_list');
    Route::get('knowledgebase-site-icon-add', 'DashboardController@siteIconAdd')->name('knowledgebase_site_icon_add');
    Route::get('knowledgebase-site-icon-edit-{id}', 'DashboardController@siteIconEdit')->name('knowledgebase_site_icon_edit');
    
    Route::group(['middleware' => 'check_demo'], function () {
        Route::post('knowledgebase-site-text-settings-update', 'DashboardController@siteTextSettingsUpdate')->name('knowledgebase_site_text_setting_update');
        Route::post('knowledgebase-site-settings-update', 'DashboardController@siteSettingsUpdate')->name('knowledgebase_site_setting_update');
        Route::post('knowledgebase-icon-update', 'DashboardController@siteIconUpdate')->name('knowledgebase_site_icon_update');
        Route::get('knowledgebase-icon-delete-{id}', 'DashboardController@siteIconDelete')->name('knowledgebase_site_icon_delete');
    });
});
