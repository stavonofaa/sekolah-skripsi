@extends('layouts.admin')

@section('title_page')
    Data Siswa
@endsection

@section('content')
    <div class="mt-3">
        <table id="table" class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Tanggal Buat</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users_siswa as $item)
                    <tr>
                        <td>
                            <h6>{{ $item->name }}</h6>
                        </td>
                        <td>
                            <h6>{{ $item->email }}</h6>
                        </td>
                        <td>
                            <h6>{{ $item->role }}</h6>
                        </td>
                        <td>
                            <h6>{{ $item->created_at }}</h6>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
