@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>GPT API Management</h2>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">API Configuration</h5>
                    <form action="{{ route('admin.gpt.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="api_key" class="form-label">GPT API Key</label>
                            <input type="text" class="form-control" id="api_key" name="api_key" value="{{ old('api_key', config('services.gpt.api_key')) }}">
                        </div>
                        <div class="mb-3">
                            <label for="api_host" class="form-label">GPT API Host</label>
                            <input type="text" class="form-control" id="api_host" name="api_host" value="{{ old('api_host', config('services.gpt.api_host')) }}">
                        </div>
                        <div class="mb-3">
                            <label for="api_url" class="form-label">GPT API URL</label>
                            <input type="text" class="form-control" id="api_url" name="api_url" value="{{ old('api_url', config('services.gpt.api_url')) }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Save API Configuration</button>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Default Analysis Questions</h5>
                    <form action="{{ route('admin.gpt.questions') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="default_question" class="form-label">Default Question</label>
                            <textarea class="form-control" id="default_question" name="default_question" rows="3">{{ old('default_question', config('services.gpt.default_question')) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="follow_up_questions" class="form-label">Follow-up Questions</label>
                            <div id="follow-up-questions">
                                @foreach(old('follow_up_questions', config('services.gpt.follow_up_questions', [])) as $index => $question)
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" name="follow_up_questions[]" value="{{ $question }}" placeholder="Enter follow-up question">
                                    <button type="button" class="btn btn-danger" onclick="removeQuestion(this)">Remove</button>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" onclick="addQuestion()">Add Follow-up Question</button>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Questions</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addQuestion() {
    const container = document.getElementById('follow-up-questions');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="follow_up_questions[]" placeholder="Enter follow-up question">
        <button type="button" class="btn btn-danger" onclick="removeQuestion(this)">Remove</button>
    `;
    container.appendChild(div);
}

function removeQuestion(button) {
    button.parentElement.remove();
}
</script>
@endsection 