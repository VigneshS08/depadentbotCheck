@extends("layouts.master")
@section("content")
 <table class="table table-striped">
    <thead>
        <tr>
        <th scope="col">S.No</th>
        <th scope="col">Forms</th>
        <th scope="col">Modified At</th>
        <th scope="col">ID</th>
        <th scope="col">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($forms as $key => $form)
            <tr>
                <th scope="row">{{$key+1}}</th>
                <td>{{$form['name']}}</td>
                <td>{{$form['modifiedAt']}}</td>
                <td>{{$form['id']}}</td>
                <td>
                    <a href="{{route('endatix.show', ['id'=>$form['id']])}}" class="btn btn-primary">View</a>
                    <a href="{{route('endatix.submission', ['id'=>$form['id']])}}" class="btn btn-primary">Submissions</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
