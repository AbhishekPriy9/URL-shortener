<div class="container">
    <div class="card card-light">
        <div class="d-flex justify-content-between m-5">
            <h5>Short URLs / <span class="text-muted">Create</span></h5>
            <div>
                <a href="{{ $back ?? 'javascript:void(0)' }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
        <form action="{{ $action ?? 'javascript:void(0)' }}" class="m-5 mt-0" method="POST">
            @csrf

            <label for="long_url" class="mt-3">Enter long URL</label>
            <input type="url" name="long_url" id="long_url" class="form-control" placeholder="Enter  long URL">
            @error('long_url')
                <span class="text-danger">{{ $message }}</span>
            @enderror

            <button type="submit" class="btn btn-primary mt-4">Generate Short URL</button>
        </form>
    </div>
</div>
