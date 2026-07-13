@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="text-3xl"
                style="color: #2f5365 font-size: 26px;
                    font-weight: 700;
                    color: #2a1a05;
                    letter-spacing: 1.5px;
                    font-family: Georgia, serif;
                    margin: 0;
                }">
                Footer Settings</h4>
            <a href="{{ route('admin.footer.edit') }}"
                style="color: #2a1a05;
                font-family: Georgia, serif;
                margin: 0;
                color:white;
                background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);"
                class="btn">
                Edit Footer
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="200">Tagline</th>
                        <td>{{ $settings->tagline ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ strip_tags($settings->description) ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Copyright Text</th>
                        <td>{{ $settings->copyright_text ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>YouTube</th>
                        <td>{{ $settings->youtube_url ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Instagram</th>
                        <td>{{ $settings->instagram_url ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Twitter</th>
                        <td>{{ $settings->twitter_url ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $settings->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $settings->contact_phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>GST Invoice Number</th>
                        <td>{{ $settings->gst_in ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $settings->contact_address ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection
