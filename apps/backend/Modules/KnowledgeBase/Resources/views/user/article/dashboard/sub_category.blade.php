   <div class="sub_title h-100">
        <div class="d-flex justify-content-center flex-column align-items-center pt-3">
            <span class="card-top-icon mb-3">
                <i class="fa fa-graduation-cap" aria-hidden="true"></i>
            </span>
            <h5>{{$subCat->name}} ({{ isset($subCatArticleList[0]) ? $subCatArticleList->count() : 0}})</h5>
        </div>
            <ul class="m-0 d-flex justify-content-center flex-column align-items-center">
                @if (isset($subCatArticleList[0]))
                    @foreach ($subCatArticleList as $key => $article)
                    @if($key < 4)
                        <li class="article-list">
                            <a class="p_color py-2 d-flex align-items-center" href="{{route('articleDetails',$article->unique_code)}}">
                                <span class="sub_icon">
                                    <i class="{{$article->icon_class}}" aria-hidden="true"></i>
                                </span>
                                {{Str::limit($article->title, 30)}}
                            </a>
                        </li>
                    @endif
                    @endforeach
                @endif
            </ul>
    </div>

<div class="details-button">
    <a
        href="{{route('articleList',$subCat->unique_code)}}">{{__('show more')}} <i class="ms-1 fa fa-angle-right" aria-hidden="true"></i>
    </a>
</div>
