<h1 class="dash-page-title">Pro + advertising ledger</h1>
<form method="get" class="dash-card"><div class="dash-card-body" style="display:flex;gap:10px;flex-wrap:wrap">
  <select name="type"><option value="">All products</option><option value="pro" @selected(request('type')==='pro')>Pro</option><option value="ad" @selected(request('type')==='ad')>Advertising</option></select>
  <input name="status" value="{{ request('status') }}" placeholder="Status">
  <input name="gateway" value="{{ request('gateway') }}" placeholder="Gateway">
  <input name="reference" value="{{ request('reference') }}" placeholder="Reference">
  <input type="date" name="from" value="{{ request('from') }}"><input type="date" name="to" value="{{ request('to') }}">
  <button class="dash-btn dash-btn-primary">Filter</button>
  <a class="dash-btn" href="{{ route('admin.operations.payments.csv', request()->query()) }}">Export CSV</a>
</div></form>
<div style="display:flex;gap:12px;flex-wrap:wrap;margin:16px 0">@foreach($summary as $status => $values)<div class="dash-card"><div class="dash-card-body"><strong>{{ ucfirst($status) }}</strong><br>{{ $values['count'] }} records · {{ number_format($values['amount'], 2) }}</div></div>@endforeach</div>
<div class="dash-card"><div class="dash-table-wrap"><table class="dash-table">
<thead><tr><th>Date</th><th>Product</th><th>Customer</th><th>Gateway</th><th>Reference</th><th>Status</th><th>Amount</th></tr></thead>
<tbody>@forelse($ledger as $row)<tr><td>{{ $row->created_at }}</td><td>{{ strtoupper($row->type) }}</td><td>{{ $row->customer }}</td><td>{{ $row->gateway }}</td><td><code>{{ $row->reference }}</code></td><td>{{ $row->status }}</td><td>{{ $row->amount === null ? 'Unknown' : trim(($row->currency ?? '') . ' ' . number_format($row->amount, 2)) }}</td></tr>@empty<tr><td colspan="7">No matching payments.</td></tr>@endforelse</tbody>
</table></div><div class="dash-card-body">{{ $ledger->links() }}</div></div>
