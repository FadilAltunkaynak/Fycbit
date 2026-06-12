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


{!! mainMenuRenderAddon('blogDashboard',__('Blog Dashboard'),$menu ?? '','blog-dashboard','dashboard.svg') !!}
{!! mainMenuRenderAddon('allBlogPage',__('Blogs'),$menu ?? '','blog-create','user.svg') !!}

{!! subMenuRendererAddon(__('Blog Category'),$menu ?? '', 'category','user.svg',[
    ['route' => 'CategoryPage', 'title' => __('Main Category'),'tab' => $sub_menu ?? '', 'tab_compare' => 'main_category', 'route_param' => NULL ],
    ['route' => 'SubCategoryPage', 'title' => __('Sub Category'),'tab' => $sub_menu ?? '', 'tab_compare' => 'sub_category', 'route_param' => NULL ],
]) !!}
{!! mainMenuRenderAddon('BlogComment',__('Comments'),$menu ?? '','comment','user.svg') !!}

{{-- {!! mainMenuRenderAddon('BlogCustomPages',__('Custom Page'),$menu ?? '','custom-pages','user.svg') !!} --}}
{!! mainMenuRenderAddon('BlogSettings',__('Blog Settings'),$menu ?? '','blog-settings','user.svg') !!}


{!! mainMenuRenderAddon('newsDashboard',__('News Dashboard'),$menu ?? '','dashboard','dashboard.svg') !!}
{!! mainMenuRenderAddon('adminDashboard',__('Admin Dashboard'),$menu ?? '','dashboard','dashboard.svg') !!}

            </ul>
        </nav>
    </div><!-- /sidebar menu -->

</div>
