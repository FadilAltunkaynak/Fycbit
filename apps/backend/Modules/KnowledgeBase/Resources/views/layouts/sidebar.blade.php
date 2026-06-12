<div class="sidebar">
    <!-- logo -->
    <div class="logo">
        <a href="{{route('adminDashboard')}}">
            <img src="{{show_image(Auth::user()->id,'logo')}}" class="img-fluid" alt="">
        </a>
    </div><!-- /logo -->

    <!-- sidebar menu -->
    <div class="sidebar-menu">
        <nav>
            <ul id="metismenu">
                    {!! mainMenuRenderer('adminDashboard',__('Admin Dashboard'),$menu ?? '','dashboard','dashboard.svg') !!}
                    {!! mainMenuRenderer('knowledgebase_dashboard',__('Knowledge Base Home'),$menu ?? '','knowledgebase_dashboard','dashboard.svg') !!}
                    
                    {!! subMenuRenderer(__('Knowledge Base'),$menu ?? '', 'knowledgebase_settings','FAQ.svg',[
                        ['route' => 'knowledgebase_categoryList', 'title' => __('Category List'),'tab' => $sub_menu ?? '', 'tab_compare' => 'category', 'route_param' => NULL ],
                        ['route' => 'knowledgebase_subCategoryList', 'title' => __('Sub Category List'),'tab' => $sub_menu ?? '', 'tab_compare' => 'sub-category', 'route_param' => NULL ],
                        ['route' => 'knowledgebase_articleList', 'title' => __('Article List'),'tab' => $sub_menu ?? '', 'tab_compare' => 'article', 'route_param' => NULL ],
                    ]) !!}

                    {!! subMenuRenderer(__('Support Ticket'),$menu ?? '', 'knowledgebase_support','user.svg',[
                        ['route' => 'support_category_list', 'title' => __('Support Category List'),'tab' => $sub_menu ?? '', 'tab_compare' => 'support-category', 'route_param' => NULL ],
                        ['route' => 'support_project_list', 'title' => __('Support Project List'),'tab' => $sub_menu ?? '', 'tab_compare' => 'support-project', 'route_param' => NULL ],
                       
                    ]) !!}

                    {!! subMenuRenderer(__('Ticket List'),$menu ?? '', 'knowledgebase_support_list','Notification.svg',[
                        ['route' => 'support_ticket_list', 'title' => __('All Ticket'),'tab' => $sub_menu ?? '', 'tab_compare' => 'support-all-ticket', 'route_param' => null ],
                        ['route' => 'support_ticket_list', 'title' => __('My Ticket'),'tab' => $sub_menu ?? '', 'tab_compare' => TICKET_ASSIGN_STATUS_SELF, 'route_param' => ['assigned_type'=>TICKET_ASSIGN_STATUS_SELF] ],
                        ['route' => 'support_ticket_list', 'title' => __('Unassigned Ticket'),'tab' => $sub_menu ?? '', 'tab_compare' => TICKET_ASSIGN_STATUS_UNASSIGNED, 'route_param' => ['assigned_type'=>TICKET_ASSIGN_STATUS_UNASSIGNED] ],
                        ['route' => 'support_ticket_list', 'title' => __('Assigned Ticket'),'tab' => $sub_menu ?? '', 'tab_compare' => TICKET_ASSIGN_STATUS_ASSIGNED, 'route_param' => ['assigned_type'=>TICKET_ASSIGN_STATUS_ASSIGNED] ],
                        ['route' => 'support_ticket_list', 'title' => __('Unseen Ticket'),'tab' => $sub_menu ?? '', 'tab_compare' => 'unseen-by-agent', 'route_param' => ['is_seen'=>SEEN] ],
                        ['route' => 'support_ticket_list', 'title' => __('Seen Ticket'),'tab' => $sub_menu ?? '', 'tab_compare' => 'seen-by-agent', 'route_param' => ['is_seen'=>UNSEEN] ],
                        
                    ]) !!}

                    {!! subMenuRenderer(__('Settings'),$menu ?? '', 'knowledgebase_support_settings','settings.svg',[
                        ['route' => 'knowledgebase_site_settings', 'title' => __('Site Settings'),'tab' => $sub_menu ?? '', 'tab_compare' => 'knowledgebase-site-settings', 'route_param' => null ],
                        ['route' => 'knowledgebase_site_text_settings', 'title' => __('Site Text Update'),'tab' => $sub_menu ?? '', 'tab_compare' => 'knowledgebase-site-text-settings', 'route_param' => null ],
                        ['route' => 'knowledgebase_site_icon_list', 'title' => __('Icon Settings'),'tab' => $sub_menu ?? '', 'tab_compare' => 'knowledgebase-icon-settings', 'route_param' => null ],
                        
                    ]) !!}
            </ul>
        </nav>
    </div><!-- /sidebar menu -->

</div>
