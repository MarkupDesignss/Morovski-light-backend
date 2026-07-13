@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        
        <div class="card shadow-sm border-0" >
            <div class="card-header"  style="
                font-family: 'Cormorant Garamond', serif;
                font-size: 2.1rem;
                font-weight: 700;
                color: var(--charcoal);
                letter-spacing: -0.5px;
                margin: 0 0 4px;
                line-height: 1;">Support Tickets</h2>
            </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-hover align-middle mb-0" style="background: #1f110a; color: #e2dcd8;">
                    <thead style="background: #2c1810; color: #f8f9fa;">
                        <tr>
                            <th scope="col" style="padding: 1rem; font-weight: 500;">Sr. No.</th>
                            <th scope="col" style="padding: 1rem; font-weight: 500;">User</th>
                            <th scope="col" style="padding: 1rem; font-weight: 500;">Order</th>
                            <th scope="col" style="padding: 1rem; font-weight: 500;">Query Type</th>
                            <!--<th scope="col" style="padding: 1rem; font-weight: 500;">Status</th>-->
                            <th scope="col" style="padding: 1rem; font-weight: 500;">Created</th>
                            <th scope="col" style="padding: 1rem; font-weight: 500;" width="100">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr style="border-bottom: 1px solid #3a251c;">
                                <td style="padding: 1rem; vertical-align: middle;">{{ $loop->iteration }}</td>
                                <td style="padding: 1rem; vertical-align: middle;">
                                    <strong >{{ $ticket->full_name }}</strong>
                                    <br>
                                    <small style="color: #a88d7a;">{{ $ticket->email }}</small>
                                </td>
                                <td style="padding: 1rem; vertical-align: middle; color: #cbb09c;">
                                    {{ $ticket->order_number }}</td>
                                <td style="padding: 1rem; vertical-align: middle;">
                                    <span class="badge px-3 py-2"
                                        style="background: #2c1810; color: #e6d5c3; font-weight: 500; border-radius: 40px;">
                                        {{ ucwords(str_replace('_', ' ', $ticket->query_type)) }}
                                    </span>
                                </td>
                                <!--<td style="padding: 1rem; vertical-align: middle;">-->
                                <!--    @if ($ticket->status == 'in_progress')-->
                                <!--        <span class="badge px-3 py-2"-->
                                <!--            style="background: #b86f2c; color: #fff; border-radius: 40px;">In-->
                                <!--            Progress</span>-->
                                <!--    @elseif($ticket->status == 'resolved')-->
                                <!--        <span class="badge px-3 py-2"-->
                                <!--            style="background: #4f7a5c; color: #fff; border-radius: 40px;">Resolved</span>-->
                                <!--    @else-->
                                <!--        <span class="badge px-3 py-2"-->
                                <!--            style="background: #5a3e2e; color: #e6d5c3; border-radius: 40px;">{{ $ticket->status }}</span>-->
                                <!--    @endif-->
                                <!--</td>-->
                                <td style="padding: 1rem; vertical-align: middle; color: #b69b81;">
                                    {{ $ticket->created_at->format('d M Y h:i A') }}</td>
                                <td style="padding: 1rem; vertical-align: middle;">
                                    <a href="{{ route('admin.support-tickets.show', $ticket->id) }}" class="btn btn-sm px-3"
                                        style="background: #2c1810; color: #f0ebe6; border: 1px solid #4a2e20; border-radius: 30px;">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5" style="color: #a88d7a;">No tickets found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4 px-3 pb-3">
                    {{ $tickets->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
