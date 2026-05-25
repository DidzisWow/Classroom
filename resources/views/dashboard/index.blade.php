@extends('layouts.app')
@section('title', 'Dashboard — ClassNova')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-sub">{{ now()->format('l, F j') }}</p>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['classes'] }}</div>
            <div class="stat-label">My Classes</div>
            <div class="stat-glow"></div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['assignments'] }}</div>
            <div class="stat-label">Assignments</div>
            <div class="stat-glow"></div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['pending'] }}</div>
            <div class="stat-label">Pending</div>
            <div class="stat-glow"></div>
        </div>
        @if(auth()->user()->role === 'teacher' || auth()->user()->role === 'admin')
        <div class="stat-card">
            <div class="stat-value">{{ $stats['students'] }}</div>
            <div class="stat-label">Students</div>
            <div class="stat-glow"></div>
        </div>
        @endif
    </div>

    <div class="dash-grid">

        {{-- Recent Classes --}}
        <section class="dash-section">
            <div class="section-head">
                <h2 class="section-title">My Classes</h2>
                <a href="{{ route('classes.index') }}" class="section-link">View all →</a>
            </div>

            @if($classes->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">⬡</div>
                    <p>No classes yet.</p>
                    @if(auth()->user()->role === 'teacher')
                        <a href="{{ route('classes.create') }}" class="btn-primary">Create a Class</a>
                    @else
                        <p class="empty-hint">Ask your teacher for a class code.</p>
                    @endif
                </div>
            @else
                <div class="class-cards">
                    @foreach($classes->take(4) as $class)
                        <a href="{{ route('classes.show', $class) }}" class="class-card">
                            <div class="class-card-accent" style="background: {{ $class->color ?? '#00e5ff' }}"></div>
                            <div class="class-card-body">
                                <h3 class="class-card-name">{{ $class->name }}</h3>
                                <p class="class-card-teacher">{{ $class->teacher->name ?? 'Unknown' }}</p>
                                <div class="class-card-meta">
                                    <span>{{ $class->assignments_count ?? 0 }} assignments</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Recent Activity --}}
        <section class="dash-section">
            <div class="section-head">
                <h2 class="section-title">Recent Activity</h2>
            </div>

            @if($recentAssignments->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">◌</div>
                    <p>No recent activity.</p>
                </div>
            @else
                <div class="activity-list">
                    @foreach($recentAssignments as $assignment)
                        <a href="{{ route('assignments.show', $assignment) }}" class="activity-item">
                            <div class="activity-dot {{ $assignment->type === 'assignment' ? 'dot-cyan' : 'dot-white' }}"></div>
                            <div class="activity-body">
                                <p class="activity-title">{{ $assignment->title }}</p>
                                <p class="activity-meta">{{ $assignment->classroom->name }} · {{ $assignment->created_at->diffForHumans() }}</p>
                            </div>
                            @if($assignment->due_date)
                                <div class="activity-due {{ $assignment->due_date < now() ? 'due-past' : '' }}">
                                    {{ \Carbon\Carbon::parse($assignment->due_date)->format('M j') }}
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
