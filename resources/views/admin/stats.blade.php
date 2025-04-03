@extends('layouts.admin')

@section('title', 'Thống kê nạp tiền')

@section('content')
<div class="admin-table-container">
    <h2>Thống kê nạp tiền</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Ngày</th>
                <th>Tổng tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats as $stat)
                <tr>
                    <td>{{ $stat->date }}</td>
                    <td>{{ number_format($stat->total, 2) }} VND</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection