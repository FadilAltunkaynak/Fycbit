<div class="container">
        <div class="row">
            <div class="text-center text-white mt-5 pt-5">
                <h1>{{$allsettings['knowledgebase_page_cover_first_title']}}</h1>
                <p>{{$allsettings['knowledgebase_page_cover_second_title']}}</p>
            </div>

            <form action="{{route('articleSearch')}}" method="POST">
                @csrf
                <div class="input-group my-3 mx-auto pb-5 search_box">
                    <input name="search" type="text" class="form-control" 
                        placeholder="{{__('Search')}}" id="search-input-value"
                        autocomplete="off"/>
                    <button class="btn btn-secondary input-group-append px-3 px-md-4 text-white rounded-end" type="submit">
                        <i class="fa fa-search" aria-hidden="true"></i>
                    </button>
                    <div class="bg-white search-filter ps-1 rounded" id="append-search-result">

                    </div>
                </div>
            </form>
        </div>
    </div>
