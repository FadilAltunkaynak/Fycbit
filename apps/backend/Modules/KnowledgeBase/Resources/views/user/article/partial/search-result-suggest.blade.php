@if ($article_list->count() > 0)
    @foreach ($article_list as $article)

        <a  href="{{route('articleDetails',$article->unique_code)}}">
            
                <b>{{$article->title}}</b>
            
        </a>

    @endforeach
@else
<a href="#">
    {{__('No Result found')}}
</a>
@endif