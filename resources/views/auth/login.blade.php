<x-layout>
    @guest
        <div class="container text-center mt-5">
            <h3 class="mb-5">Login</h3>
            <div class="d-flex justify-content-center">
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <form action="{{ route('login') }}" class="w-75" method="POST">
                    @csrf
                    <label for="Email" class="mt-3">Email</label>
                    <input type="email" name="email" id="Email" class="form-control" placeholder="Enter your email">
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <label for="Password" class="mt-3">Password</label>
                    <input type="password" name="password" id="Password" class="form-control"
                        placeholder="Enter your password">
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <button type="submit" class="btn btn-primary mt-4">Login</button>
                </form>
            </div>
        </div>
    @endguest

    @auth
        <div class="container text-center mt-5">
            @switch(auth()->user()->role)
                @case('SuperAdmin')
                    @php $roleName = 'Super Admin '; @endphp
                    @php $dashboardRoute = route('super.dashboard.index'); @endphp
                @break

                @case('Admin')
                    @php $roleName = 'Admin '; @endphp
                    @php $dashboardRoute = route('admin.dashboard.index'); @endphp
                @break

                @default
                    @php $roleName = 'Member '; @endphp
                    @php $dashboardRoute = route('member.dashboard.index'); @endphp
            @endswitch
            <a href="{{ $dashboardRoute }}" class="mb-5">{{ $roleName }}Dashboard</a>
        </div>
    @endauth
</x-layout>
