@extends('knowledgebase::layouts.user_master')

@section('content')
<!--navigation-->
 @php($allsettings = knowledgebaseSupportSettings())
<div class="container mt-4">
    <h4 class="text-center"><b>{{ __('Search result for ') }} {{ $search }}</b></h3>
    <div class="row">
        @if (isset($article_list[0]))
            @foreach ($article_list as $article)

                <div class="card col-md-12 m-1">
                    <div class="card-body">
                        <a href="{{route('articleDetails',$article->unique_code)}}">
                        <div class="row">
                            <p>
                                {{$article->title}}
                            </p>
                        </div>
                    </a>
                    </div>
                </div>

            @endforeach
            @else
            <p class="text-center mt-5 text-danger">{{ __('No article found') }}</p>
        @endif
    </div>
</div>

@endsection

@section('script')

@endsection
