@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Tenant Dashboard</h1>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @if($data->isEmpty())
                    <tr>
                        <td>No data available.</td>
                    </tr>
                @else
                    @foreach($data as $item)
                        <tr>
                            <td>{{ $item->data }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
@endsection
