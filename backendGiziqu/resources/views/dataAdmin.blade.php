@extends('layout')

@section('konten')
<div class="container mt-4">
    @if(isset($msg))
    <div id="alert" class="alert alert-success alert-dismissible" role="alert">
        {{ $msg }}
    </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th colspan="5" class="text-center">Data Admin</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td align="center">Foto</td> <!-- Kolom baru untuk menampilkan foto -->
                <td align="center">Nama</td>
                <td align="center">Username</td>
                <td align="center">Email</td>
                <td align="center">Password</td>
                <td align="center">Aksi</td>
            </tr>
            @isset($data)
            @foreach($data as $user)
            <tr>
                <td align="center"><img src="{{ $user['foto'] }}" alt="{{ $user['name'] }}" style="max-width: 25px;"></td> <!-- Menampilkan foto dengan URL dari Firebase Storage -->
                <td>{{ $user['name'] }}</td>
                <td>{{ $user['username'] }}</td>
                <td>{{ $user['email'] }}</td>
                <td>{{ $user['password'] }}</td>
                <td><a href="{{ route('deleteAdmin.delete', $user['username']) }}" class="btn btn-danger">Delete</a></td>
            </tr>
            @endforeach
            @endisset
        </tbody>
    </table>
</div>

@if(isset($msg))
<script>
    // Mengatur timeout untuk menghilangkan alert setelah 4 detik
    setTimeout(function() {
        document.getElementById('alert').style.display = 'none';
    }, 4000); // Waktu dalam milidetik (4 detik = 4000 milidetik)
</script>
@endif
@endsection