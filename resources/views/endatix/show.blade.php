@extends('layouts.master')

@section('content')
<div class="endatix-embed-wrapper">
    <iframe
        src="{{ $signedUrl }}"
        width="100%"
        height="800"
        frameborder="0"
        referrerpolicy="no-referrer"
        title="Endatix Form {{ $id }}">
    </iframe>
</div>

<style>
    .endatix-embed-wrapper { width: 100%; }
    .endatix-embed-wrapper iframe {
        display: block;
        border: 0;
        min-height: 800px;
    }
</style>
@endsection
