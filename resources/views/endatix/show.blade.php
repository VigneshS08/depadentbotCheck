@extends('layouts.master')

@section('content')
   <iframe
        src={{ @$url }}
        width="100%"
        height="800"
        allowTransparency
        frameborder="0">
@endsection
