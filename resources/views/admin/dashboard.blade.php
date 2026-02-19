@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1>Reports snapshot</h1>
    <p class="page-sub">Overview of startups, submissions, and engagement.</p>
    <div class="stats-grid">
        <div class="card"><strong>{{ $totalStartups }}</strong><br>Total startups</div>
        <div class="card"><strong>{{ $pendingSubmissions }}</strong><br>Pending submissions</div>
        <div class="card"><strong>{{ $claimed }}</strong><br>Claimed</div>
        <div class="card"><strong>{{ $unclaimed }}</strong><br>Unclaimed</div>
    </div>
    <h2>Recent submissions</h2>
    <table>
        <thead><tr><th>Name</th><th>Submitted</th><th>Actions</th></tr></thead>
        <tbody>
            @forelse($recentSubmissions as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->submitted_at?->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.startups.edit', $s) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.startups.approve', $s) }}" method="POST" class="form-inline">@csrf<button type="submit" class="btn btn-sm">Approve</button></form>
                        <form action="{{ route('admin.startups.reject', $s) }}" method="POST" class="form-inline">@csrf<button type="submit" class="btn btn-danger btn-sm">Reject</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">No pending submissions.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="back-link"><a href="{{ route('admin.submissions.index') }}" class="btn">View all submissions</a></p>
    <h2 class="section-title">Most viewed</h2>
    <table>
        <thead><tr><th>Name</th><th>Views</th></tr></thead>
        <tbody>
            @forelse($mostViewed as $s)
                <tr><td><a href="{{ route('startups.show', $s->slug) }}">{{ $s->name }}</a></td><td>{{ $s->view_count }}</td></tr>
            @empty
                <tr><td colspan="2">None yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <h2 class="section-title-sm">Top MRR</h2>
    <table>
        <thead><tr><th>Name</th><th>MRR</th></tr></thead>
        <tbody>
            @forelse($topMrr as $s)
                <tr><td><a href="{{ route('startups.show', $s->slug) }}">{{ $s->name }}</a></td><td>${{ number_format($s->mrr) }}</td></tr>
            @empty
                <tr><td colspan="2">None yet.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
