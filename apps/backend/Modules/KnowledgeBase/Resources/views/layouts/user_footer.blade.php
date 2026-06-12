<footer class="footer_bg py-3">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <div
          class="footer_text d-flex align-items-center h-100 justify-content-center justify-content-md-start">
          <p class="pe-2">{{allsetting('copyright_text')}} </p>
          <a href="{{allsetting('exchange_url')}}"> {{allsetting('app_title')}}</a>
                </div>
            </div>
            @php($social_media_list = socialMediaListSupport()['success']?socialMediaListSupport()['data']:null)
            <div class="col-md-6">
                <div class="d-flex gap-3 justify-content-center justify-content-md-end mt-3 mt-md-0">
                    @if(isset($social_media_list))
                        @foreach ($social_media_list as $social_media_item)
                        <a href="{{$social_media_item->media_link}}" class="footer_icon">
                            <img src="{{$social_media_item->media_icon}}" alt="">
                        </a>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>
