@extends('layouts.master')

@section('content')
<table class="table table-striped">
    <thead>
        <tr>
        <th scope="col">S.No</th>
        <th scope="col">Created At</th>
        <th scope="col">Is Complete</th>
        <th scope="col">Completed at</th>
        <th scope="col">Completion Time</th>
        <th scope="col">Status</th>
        <th scope="col">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($submission as $key => $submission_list)
            <tr>
                <th scope="row">{{$key+1}}</th>
                <td>{{$submission_list['createdAt']}}</td>
                <td>{{$submission_list['isComplete']}}</td>
                <td>{{$submission_list['completedAt']}}</td>
                <td>{{$submission_list['completedAt']}}</td>
                <td>{{$submission_list['status']}}</td>
                <td>
                    <a href="{{route('endatix.singlesubmission', ['form_id'=>$submission_list['formId'],'submission_id'=>$submission_list['id']])}}" class="btn btn-primary">View</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
