@include('email.header_new')
<p>Hi {{$name}},</p>
<p>
<p>
    {{$subject}}
</p>
<p>
    {{$details}}
</p>

@include('email.footer_new')
