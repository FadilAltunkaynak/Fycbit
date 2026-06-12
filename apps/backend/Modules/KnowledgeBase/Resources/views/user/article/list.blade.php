@extends('knowledgebase::layouts.user_master')
@section('content')
<!-- body text section start -->
<section class="mb-5 mt-4">
    <div class="container">
        <h2 class="text-center pb-3"><b>{{ $sub_category_details->main_category_title }} / {{ $sub_category_details->name }} ({{ __('Artical List') }})</b></h2>
        <hr>
        <div class="row">
                @if (isset($article_list[0]))
                    @foreach ($article_list as $item)
                        <div class="col-md-6 col-lg-4 mt-4 pt-4 ">
                            <div class="sub_title px-4 pt-4 pb-1 h-100">
                                <h4 class="fw_600 pt-3 mb-0">
                                    <span class="me-2 h5"><i class="{{ $item->icon_class }}"></i></span> {{$item->title}}
                                </h4>
                                <small class="article-date">{{$item->created_at}}</small>
                                <p class="p_color pt-3">
                                    {!! Str::limit($item->description,250) !!}
                                </p>
                            </div>
                            <div class="details-button">
                                <a
                                    href="{{route('articleDetails',$item->unique_code)}}">{{__('view more')}} <i class="ms-1 fa fa-long-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif
            {{-- <div class="col-md-12 mt-5">
                @if (isset($article_list[0]))
                <div class="d-flex justify-content-center">
                    {!! $article_list->links() !!}
                </div>
                @endif
            </div> --}}
        </div>
    </div>
</section>
<!--  body text section end -->
@endsection
