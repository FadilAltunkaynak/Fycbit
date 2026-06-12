@extends('knowledgebase::layouts.user_master')
@section('content')
<!--  body text section start -->
<section class="mb-5 pb-5">
    <div class="container">
        @if (isset($category_list))
            @foreach ($category_list as $category)
                <div class="row mt-5 pt-5">
                        <a class="d-flex align-items-center title-icon" href="{{ route('knowledgeCategory',$category->unique_code) }}">
                            <i class="{{$category->icon_class}}" aria-hidden="true"></i>
                            <h3 class="fw_600 m-0">{{$category->name}}</h3>
                        </a>
                    @if (isset($category->knbSubCategory))
                        @foreach ($category->knbSubCategory as $keyD => $sub_category)
                            @if($keyD < 3)
                                <div class="col-md-6 col-lg-4 mt-5 pt-4 pt-lg-0">
                                    @php $subCat = $sub_category @endphp
                                    @php $subCatArticleList = $sub_category->knbArticles @endphp
                                    @include('knowledgebase::user.article.dashboard.sub_category')
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            @endforeach
        @endif

    </div>
</section>
<!--  body text section end -->

@endsection

@section('script')
    <script>
        jQuery(document).ready(function () {

        Pusher.logToConsole = true;
        let user_id = '{{Auth::id()}}';

        Echo.channel('New-Ticket-Notification-Send-To-User-'+user_id)
            .listen('.Notification', (data) => {
                console.log(data);

            })
    });

    $('#recipeCarousel').carousel({
    interval: 10000
    })

    $('.carousel .carousel-item').each(function(){
        var minPerSlide = 4;
        var next = $(this).next();
        if (!next.length) {
        next = $(this).siblings(':first');
        }
        next.children(':first-child').clone().appendTo($(this));

        for (var i=0;i<minPerSlide;i++) {
            next=next.next();
            if (!next.length) {
                next = $(this).siblings(':first');
            }

            next.children(':first-child').clone().appendTo($(this));
        }
    });

    // slider 
    $(document).ready(function(){
        $(".owl-carousel").owlCarousel();
    });
    $('.owl-carousel').owlCarousel({
    rtl:true,
    loop:true,
    margin:10,
    nav:true,
    responsive:{
        0:{
            items:1
        },
        600:{
            items:3
        },
        1000:{
            items:5
        }
    }
})

    </script>
@endsection
