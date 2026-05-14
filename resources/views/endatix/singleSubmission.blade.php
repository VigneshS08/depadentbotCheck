@extends('layouts.master')

@section('content')
<a href="{{ route('endatix.submission', ['id' => $submissionData['formId']]) }}" class="btn btn-secondary mb-3">&larr; Back to submissions</a>

<div class="card mb-4">
    <div class="card-header">
        <h3 class="mb-0">Submission Details</h3>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Submission ID</dt>
            <dd class="col-sm-9">{{ $submissionData['id'] ?? '-' }}</dd>

            <dt class="col-sm-3">Form ID</dt>
            <dd class="col-sm-9">{{ $submissionData['formId'] ?? '-' }}</dd>

            <dt class="col-sm-3">Created At</dt>
            <dd class="col-sm-9">{{ $submissionData['createdAt'] ?? '-' }}</dd>

            <dt class="col-sm-3">Is Complete</dt>
            <dd class="col-sm-9">
                @if(!empty($submissionData['isComplete']))
                    <span class="badge bg-success">Yes</span>
                @else
                    <span class="badge bg-warning text-dark">No</span>
                @endif
            </dd>

            <dt class="col-sm-3">Completed At</dt>
            <dd class="col-sm-9">{{ $submissionData['completedAt'] ?? '-' }}</dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">{{ $submissionData['status'] ?? '-' }}</dd>
        </dl>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Submitted Answers</h3>
    </div>
    <div class="card-body">
        @if(!empty($answers))
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 30%;">Question</th>
                        <th scope="col">Answer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($answers as $question => $answer)
                        <tr>
                            <td><strong>{{ $question }}</strong></td>
                            <td>
                                @if(is_array($answer) || is_object($answer))
                                    <pre class="mb-0">{{ json_encode($answer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                @elseif(is_bool($answer))
                                    {{ $answer ? 'true' : 'false' }}
                                @else
                                    {{ $answer }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted mb-0">No answers were submitted.</p>
        @endif
    </div>
</div>
@endsection
