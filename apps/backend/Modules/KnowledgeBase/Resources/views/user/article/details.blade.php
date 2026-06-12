@extends('knowledgebase::layouts.user_master')

@section('content')
@php($user = auth()->user())
@php($notification_list = getNotificationList())
@php($allsettings = knowledgebaseSupportSettings())


<!-- body text section start -->
<section class="mb-5">
    <div class="container">
        <div class="row">
            @if (isset($article_details))
                <div class="col-md-8 mt-4">
                    <div class="main_img">
                        <img class="rounded-3" src="{{asset(FILE_KNOWLEDGE_BASE_VIEW_PATH.$article_details->feature_image)}}" alt="" />
                    </div>
                    <h2 class="fw_600 pt-3 mb-0">
                        {{$article_details->title}}
                    </h2>
                    <small class="article-date">{{$article_details->updated_at}}</small>
                    <p class="p_color pt-3">
                        {!! $article_details->description !!}
                    </p>
                    @if (isset($article_details->knbArticleSections))
                        @foreach ($article_details->knbArticleSections as $article_section)
                            <div class="py-4">
                                <h5 class="fw_600">
                                    {{$article_section->title}}
                                </h5>
                                <p class="p_color">
                                    {!! $article_section->description !!}
                                </p>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endif

            <div class="col-md-6 col-lg-4 mt-5 mt-lg-0 pt-4">
                <div class="h-100">
                    @if (isset($related_article_list))
                        @foreach ($related_article_list as $related_article)
                            <div class="sub_title p-4 mt-2">
                                <h4 class="fw_600 pt-3 mb-1">
                                    <span class="me-2 h5"><i class="fa fa-address-card"></i></span>
                                    {{$related_article->title}}
                                </h4>
                                <small class="article-date">{{$related_article->updated_at}}</small>
                                <p class="p_color pt-3">
                                    {!! Str::limit($related_article->description, 180) !!}
                                </p>
                            </div>
                            <div class="details-button">
                                <a
                                    href="{{route('articleDetails',$related_article->unique_code)}}">{{__('view more')}} <i class="ms-1 fa fa-long-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        @endforeach    

                    @endif
                </div>
                
            </div>

        </div>
    </div>
</section>
<!--  body text section end -->



@endsection
