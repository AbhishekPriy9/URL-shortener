<x-layout>
    <x-logout />
    <div class="container">
        <div class="card card-light">
            <div class=" d-flex justify-content-between m-5">
                <h5>Short URLs</h5>
                <div>
                    <a href="{{ route('member.urls.create') }}" class="btn btn-primary">Generate</a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <table class="table m-5 mt-0">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Short URL</th>
                        <th scope="col">Long URL</th>
                        <th scope="col">Generated At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($shortURLs as $su)
                        <tr>
                            <th scope="row">{{ $su->id }}</th>
                            <td>
                                <a href="{{ url($su->short_url) }}">
                                    {{ Str::limit($su->short_url, 15) }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ $su->long_url }}">
                                    {{ Str::limit($su->long_url, 15) }}
                                </a>
                            </td>
                            <td>{{ $su->created_at->format('d-m-Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mx-5">
                {{ $shortURLs->links() }}
            </div>
        </div>
    </div>

</x-layout>
