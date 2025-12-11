<x-layout>
    <x-logout />
    <div class="container">
        <div class="card card-light">
            <div class="d-flex justify-content-between m-5">
                <h5>Member / <span class="text-muted">Invite</span></h5>
                <div>
                    <a href="{{ route('admin.dashboard.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
            <form action="{{ route('admin.clients.store') }}" class="m-5 mt-0" method="POST">
                @csrf
                <label for="Name" class="mt-3">Name</label>
                <input type="name" name="name" id="Name" class="form-control" placeholder="Enter  name">
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror

                <label for="Email" class="mt-3">Email</label>
                <input type="email" name="email" id="Email" class="form-control" placeholder="Enter  email">
                @error('email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror

                <label for="Role" class="mt-3">Role</label>
                <select name="role" id="Role" class="form-control">
                    <option value="">Select a role</option>
                    <option>Admin</option>
                    <option>Member</option>
                </select>
                @error('role')
                    <span class="text-danger">{{ $message }}</span>
                @enderror

                <button type="submit" class="btn btn-primary mt-4">Send Invitation</button>
            </form>
        </div>
    </div>

</x-layout>
