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

{!! mainMenuRenderAddon('blogDashboard',__('Dashboard'),$menu ?? '','dashboarded','dashboard.svg') !!}
{!! subMenuRendererAddon(__('Blogs'),$menu ?? '', 'blog','logs.svg',[
    ['route' => 'allBlogPage', 'title' => __('Blogs'),'tab' => $sub_menu ?? '', 'tab_compare' => 'blog', 'route_param' => NULL ],
    ['route' => 'CategoryPage', 'title' => __('Main Category'),'tab' => $sub_menu ?? '', 'tab_compare' => 'main_category', 'route_param' => NULL ],
    ['route' => 'SubCategoryPage', 'title' => __('Sub Category'),'tab' => $sub_menu ?? '', 'tab_compare' => 'sub_category', 'route_param' => NULL ],
    ['route' => 'BlogComment', 'title' => __('Comments'),'tab' => $sub_menu ?? '', 'tab_compare' => 'comment', 'route_param' => NULL ],
    ['route' => 'BlogSettings', 'title' => __('Settings'),'tab' => $sub_menu ?? '', 'tab_compare' => 'settings', 'route_param' => NULL ],
]) !!}
{!! subMenuRendererAddon(__('News'),$menu ?? '', 'news','logs.svg',[
    ['route' => 'allNewsPage', 'title' => __('News'),'tab' => $sub_menu ?? '', 'tab_compare' => 'news', 'route_param' => NULL ],
    ['route' => 'newsCategoryPage', 'title' => __('Main Category'),'tab' => $sub_menu ?? '', 'tab_compare' => 'main_categorys', 'route_param' => NULL ],
    ['route' => 'NewsComment', 'title' => __('Comments'),'tab' => $sub_menu ?? '', 'tab_compare' => 'comments', 'route_param' => NULL ],
    ['route' => 'NewsSettings', 'title' => __('Settings'),'tab' => $sub_menu ?? '', 'tab_compare' => 'setting', 'route_param' => NULL ],
]) !!}
{!! mainMenuRenderAddon('adminDashboard',__('Admin Dashboard'),$menu ?? '','dashboard','trade-report.svg') !!}

            </ul>
        </nav>
    </div><!-- /sidebar menu -->

</div>
