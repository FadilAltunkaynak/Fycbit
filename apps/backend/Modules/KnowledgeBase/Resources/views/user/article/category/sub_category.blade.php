@extends('knowledgebase::layouts.user_master')
@section('content')
<!--  body text section start -->
<section class="mb-5 pb-5">
    <div class="container">
        <h1 class="text-center mt-5"><b>{{ __('Tradexpro Knowledge') }}</b></h1>
        @if (!empty($category))
            <div class="row mt-5 pt-5 pt-md-0">
                    <a class="d-flex align-items-center title-icon" href="{{ route('knowledgeCategory',$category->unique_code) }}">
                        <i class="{{$category->icon_class}}" aria-hidden="true"></i>
                        <h3 class="fw_600 m-0">{{$category->name}}</h3>
                    </a>
                @if (isset($items[0]))
                    @foreach ($items as $sub_category)
                        <div class="col-md-6 col-lg-4 mt-4 pt-4">
                            @php $subCat = $sub_category @endphp
                            @php $subCatArticleList = $sub_category->knbArticles @endphp
                            @include('knowledgebase::user.article.dashboard.sub_category')
                        </div>
                    @endforeach
                @endif
            </div>
        @endif
    </div>
</section>
<!--  body text section end -->
@endsection
@section('script')
@endsection
