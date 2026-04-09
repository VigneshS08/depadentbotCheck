@extends('layouts.master')

@section('content')
<h3>Submission Details</h3>
<p>Created At: {{$submission['createdAt']}}</p>
<p>Is Complete: {{$submission['isComplete']}}</p>
<p>Completed At: {{$submission['completedAt']}}</p>
<p>Status: {{$submission['status']}}</p>
<h3>Submitted Answers</h3>
@endsection
