<div class=" d-flex justify-content-between m-5">
    <h3>
        @php $logoutRoute = ''; @endphp
        @switch(auth()->user()->role)
            @case('SuperAdmin')
                Super Admin
                @php $logoutRoute = 'logout'; @endphp
            @break

            @case('Admin')
                Admin
                @php $logoutRoute = 'logout'; @endphp
            @break

            @default
                @php $logoutRoute = 'logout'; @endphp
                Member
        @endswitch
        Dashboard ({{ auth()->user()->name }})</h3>
    <div>
        <form action="{{ route($logoutRoute) }}" method="POST" onsubmit="return confirm('Are you sure want to logout?')">
            @csrf
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
    </div>
</div>
