@extends('layouts.admin')

@section('title', 'Ads')

@section('content')
    <h1>Ad Manager</h1>
    <p>Global AdSense client ID (Settings): {{ $adsenseClientId ?: 'Not set' }}</p>
    <p><a href="{{ route('admin.ads.create') }}" class="btn">Add ad</a></p>
    <table>
        <thead><tr><th>Slot</th><th>Type</th><th>Name</th><th>Active</th><th>Expires</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach(['above_fold'=>'Above fold','in_feed'=>'In-feed','sidebar'=>'Sidebar','in_content'=>'In-content'] as $slot=>$label)
                @foreach($ads->get($slot, []) as $ad)
                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ $ad->type }}</td>
                        <td>{{ $ad->name ?? '—' }}</td>
                        <td>{{ $ad->is_active ? 'Yes' : 'No' }}</td>
                        <td>{{ $ad->expires_at?->format('Y-m-d') ?? '—' }}</td>
                        <td><a href="{{ route('admin.ads.edit', $ad) }}" class="btn btn-sm">Edit</a>
                            <form action="{{ route('admin.ads.destroy', $ad) }}" method="POST" class="form-inline" onsubmit="return confirm('Delete?');">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm">Delete</button></form></td>
                    </tr>
                @endforeach
                @if($ads->get($slot, [])->isEmpty())
                    <tr><td>{{ $label }}</td><td colspan="5">No ad</td></tr>
                @endif
            @endforeach
        </tbody>
    </table>
@endsection
