@extends('layouts.admin')

@section('content')
    <style>
        body,
        .container,
        input,
        select,
        button,
        a,
        p,
        h2,
        label {
            font-family: Georgia, serif;
        }

        .warehouse-form-wrapper {
            padding: 2rem 0;
        }

        .warehouse-form-wrapper h2 {
            font-family: Georgia, serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #160c00;
            margin-bottom: 1.5rem;
            letter-spacing: 0.02em;
        }

        .alert-danger-custom {
            background: #fdf2f0;
            border-left: 4px solid #7a2010;
            color: #3d0800;
            padding: 0.75rem 1rem;
            border-radius: 5px;
            margin-bottom: 1.25rem;
            font-family: Georgia, serif;
            font-size: 0.88rem;
        }

        .alert-danger-custom ul {
            margin: 0;
            padding-left: 1.2rem;
        }
    </style>

    <div class="container warehouse-form-wrapper">
        <h2>Edit Warehouse</h2>

        @if ($errors->any())
            <div class="alert-danger-custom">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.warehouses.update', $warehouse->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.warehouses.form', ['warehouse' => $warehouse])
        </form>
    </div>
@endsection