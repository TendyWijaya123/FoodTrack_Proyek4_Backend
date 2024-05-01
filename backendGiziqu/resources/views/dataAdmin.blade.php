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
                <th colspan="4" class="text-center">Data Admin</th>
            </tr>
        </thead>
        <tbody>

            <tr>
                <td align="center">Nama</td>
                <td align="center">Username</td>
                <td align="center">Email</td>
                <td align="center">password</td>
                <td align="center">aksi</td>
            </tr>
            @isset($data)
            @foreach($data as $user)
            <tr>
                <td>{{ $user['name'] }}</td>
                <td>{{ $user['username'] }}</td>
                <td>{{ $user['email'] }}</td>
                <td>{{ $user['password'] }}</td>
                <td><a href="{{ route('deleteAdmin.delete', $user['username']) }}" class="btn btn-danger">delete</a></td>
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