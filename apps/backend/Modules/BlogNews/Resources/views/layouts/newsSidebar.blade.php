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


{!! mainMenuRenderAddon('newsDashboard',__('News Dashboard'),$menu ?? '','news-dashboard','dashboard.svg') !!}
{!! mainMenuRenderAddon('allNewsPage',__('News'),$menu ?? '','news-create','user.svg') !!}

{{-- {!! subMenuRenderer(__('News Category'),$menu ?? '', 'news-category','user.svg',[
    ['route' => 'newsCategoryPage', 'title' => __('Main Category'),'tab' => $sub_menu ?? '', 'tab_compare' => 'news-main_category', 'route_param' => NULL ],
    ['route' => 'newsSubCategoryPage', 'title' => __('Sub Category'),'tab' => $sub_menu ?? '', 'tab_compare' => 'news-sub_category', 'route_param' => NULL ],
]) !!} --}}
{!! mainMenuRenderAddon('newsCategoryPage',__('News Category'),$menu ?? '','news-category','user.svg') !!}
{!! mainMenuRenderAddon('NewsComment',__('Comments'),$menu ?? '','comment','user.svg') !!}

{{-- {!! mainMenuRenderAddon('NewsCustomPages',__('Custom Page'),$menu ?? '','custom-pages','user.svg') !!} --}}
{!! mainMenuRenderAddon('NewsSettings',__('News Settings'),$menu ?? '','news-settings','user.svg') !!}

{!! mainMenuRenderAddon('blogDashboard',__('Blog Dashboard'),$menu ?? '','dashboard','dashboard.svg') !!}
{!! mainMenuRenderAddon('adminDashboard',__('Admin Dashboard'),$menu ?? '','dashboard','dashboard.svg') !!}

            </ul>
        </nav>
    </div><!-- /sidebar menu -->

</div>
