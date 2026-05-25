@extends('layouts.app')
@section('title', 'Admin — ClassNova')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <h1 class="page-title">Admin Panel</h1>
        <p class="page-sub">Manage users, roles, and platform activity</p>
    </div>

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_users'] }}</div>
            <div class="stat-label">Total Users</div>
            <div class="stat-glow"></div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['teachers'] }}</div>
            <div class="stat-label">Teachers</div>
            <div class="stat-glow"></div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['students'] }}</div>
            <div class="stat-label">Students</div>
            <div class="stat-glow"></div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['classes'] }}</div>
            <div class="stat-label">Classes</div>
            <div class="stat-glow"></div>
        </div>
    </div>

    {{-- Users Table --}}
    <section class="dash-section">
        <div class="section-head">
            <h2 class="section-title">All Users</h2>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="table-user">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" class="student-avatar" alt="">
                                    @else
                                        <div class="student-avatar-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                    @endif
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.updateRole', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" onchange="this.form.submit()" class="role-select role-{{ $user->role }}">
                                        <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Student</option>
                                        <option value="teacher" {{ $user->role === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td>{{ $user->created_at->format('M j, Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.resetPassword', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-sm btn-ghost">Reset PW</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $users->links() }}</div>
    </section>

    {{-- Action History --}}
    <section class="dash-section">
        <div class="section-head">
            <h2 class="section-title">Action History</h2>
        </div>
        <div class="activity-list">
            @foreach($actionHistory as $log)
                <div class="activity-item">
                    <div class="activity-dot dot-white"></div>
                    <div class="activity-body">
                        <p class="activity-title">{{ $log->action }}</p>
                        <p class="activity-meta">{{ $log->user->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

</div>
@endsection
