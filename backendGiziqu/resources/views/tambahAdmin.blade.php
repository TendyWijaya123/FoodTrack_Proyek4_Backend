@extends('layout')

@section('konten')
<div class="container">
    <div class="row justify-content-center"> <!-- Menengahkan secara horizontal -->
        <div class="col-md-5"> <!-- Mengatur lebar form -->
            @if(isset($msg))
            <div id="alert" class="alert alert-success alert-dismissible" role="alert">
                {{ $msg }}
            </div>
            @endif

            <form action="{{ route('tambahAdmin.admin') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" class="form-control" id="name" aria-describedby="name" placeholder="Masukkan Nama" name="name">
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" aria-describedby="username" placeholder="Masukkan Username" name="username">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" aria-describedby="email" placeholder="Masukkan Email" name="email">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" placeholder="Password" name="password">
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
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