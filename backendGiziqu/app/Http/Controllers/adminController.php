<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Contract\Database;
// use Kreait\Firebase\Contract\Storage;
use Kreait\Firebase\Contract\Storage;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

use Kreait\Firebase\Database\Query\Filter\IsNull;

use Illuminate\Http\Request;

class adminController extends Controller
{
    protected $database;
    protected $table;
    protected $storage;
    protected $auth;

    public function __construct(Database $database, Storage $storage, auth $auth)
    {
        $this->auth = $auth;
        $this->database = $database;
        $this->table = "user";
        $this->database = \App\Services\FirebaseService::connect();
        $this->storage = $storage;
    }

    public function tambah()
    {
        return view('/tambahAdmin', ['judul' => "Tambah Admin"]);
    }

    public function data()
    {
        $data = $this->database->getReference($this->table)->orderByChild('role')->equalTo("admin")->getSnapshot()->getValue();;

        // Mendapatkan URL gambar dari Firebase Storage untuk setiap admin
        if ($data != null) {
            foreach ($data as &$user) {
                $firebaseStoragePath = 'Images/Users/'; // Path di Firebase Storage tempat gambar disimpan
                $fileName = $user['foto']; // Nama file gambar yang disimpan dalam database
                $imageUrl = $this->storage->getBucket()->object($firebaseStoragePath . $fileName)->signedUrl(strtotime('+1 hour'));
                $user['foto'] = $imageUrl; // Mengganti nama file dengan URL gambar
            }
        }

        // Kirim data yang telah diperbarui ke view untuk ditampilkan
        return view('dataAdmin', ['data' => $data, 'judul' => "Data Admin"]);
    }

    public function admin(Request $request)
    {
        $role = "admin";

        $username = $request->input('username');
        $email = $request->input('email');

        $usernameExists = $this->database->getReference($this->table)->orderByChild('username')->equalTo($username)->getSnapshot()->getValue();
        $emailExists = $this->database->getReference($this->table)->orderByChild('username')->equalTo($email)->getSnapshot()->getValue();


        if (count($usernameExists) > 0 || count($emailExists) > 0) {
            return response()->json(['message' => 'Username sudah digunakan'], 422);
        }

        // Periksa keunikan email di Firebase
        $emailExists = $this->database->getReference($this->table)->orderByChild('email')->equalTo($email)->getSnapshot()->getValue();

        if (count($emailExists) > 0) {
            return response()->json(['message' => 'Email sudah digunakan'], 422);
        }

        $fileName = 'default.jpeg';
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $fileName = $foto->getClientOriginalName();
            $firebaseStoragePath = 'Images/Users/';
            $localFolder = public_path('firebase-temp-uploads') . '/';
            $localPath = $localFolder . $fileName;

            if ($foto->move($localFolder, $fileName)) {
                $uploadedfile = fopen($localPath, 'r');
                $this->storage->getBucket()->upload($uploadedfile, [
                    'name' => $firebaseStoragePath . $fileName
                ]);
                unlink($localPath);
            }
        }

        $postData = [
            'name' => $request->input("name"),
            'username' => $request->input("username"),
            'email' => $request->input("email"),
            'password' => $request->input("password"),
            'role' => $role,
            'foto' => $fileName
        ];

        $userProperties = [
            'email' => $request->input("email"),
            'emailVerified' => false,
            'password' => $request->input("password"),
            'displayName' => $request->input("name"),
            'disabled' => false,
        ];
        $this->auth->createUser($postData);
        $postRef = $this->database->getReference($this->table)->push($postData);
        if ($postRef) {
            return view('tambahAdmin', ['msg' => "Berhasil mendaftarkan admin", 'judul' => "Tambah Admin"]);
        } else {
            return view('tambahAdmin', ['msg' => "Gagal mendaftarkan admin", 'judul' => "Tambah Admin"]);
        }
    }

    public function uploadimage(Request $request) // Menggunakan request untuk mendapatkan data yang dikirim dari aplikasi Flutter
    {
        $id = $request->input('id'); // Mendapatkan id dari request
        $fileName = ''; // Inisialisasi nama file kosong

        // Proses upload gambar hanya jika ada file yang dikirim
        if ($request->hasFile('image')) {
            $image = $request->file('image'); // Mengambil file gambar dari request
            $fileName = $image->getClientOriginalName(); // Mengambil nama file asli

            // Proses penyimpanan file gambar ke Firebase Storage
            $firebaseStoragePath = 'Images/Users/'; // Path di Firebase Storage tempat gambar disimpan
            $localFolder = public_path('firebase-temp-uploads') . '/'; // Folder lokal untuk menyimpan file sementara
            $localPath = $localFolder . $fileName; // Path lokal lengkap untuk file gambar

            // Memindahkan file gambar sementara ke folder penyimpanan Firebase Storage
            if ($image->move($localFolder, $fileName)) {
                $uploadedfile = fopen($localPath, 'r'); // Membuka file gambar
                $this->storage->getBucket()->upload($uploadedfile, ['name' => $firebaseStoragePath . $fileName]); // Mengunggah file ke Firebase Storage
                unlink($localPath); // Menghapus file gambar sementara setelah diunggah
            }
        }

        // Lakukan proses update data admin dengan nama file gambar yang baru
        // Anda harus menyesuaikan ini sesuai dengan struktur data di database Anda
        $reference = $this->database->getReference($this->table)->orderByChild('username')->equalTo($id)->getSnapshot()->getValue();
        if (!empty($reference)) {
            foreach ($reference as $key => $value) {
                // Lakukan penggantian nama file gambar di dalam data admin
                $this->database->getReference($this->table . '/' . $key . '/foto')->set($fileName);
            }
        }

        // Mengembalikan respon ke aplikasi Flutter jika diperlukan
        return response()->json(['message' => 'Image uploaded successfully'], 200);
    }


    public function delete($username)
    {
        $reference = $this->database->getReference($this->table)->orderByChild('username')->equalTo($username)->getSnapshot()->getValue();
        if (!empty($reference)) {
            foreach ($reference as $key => $value) {
                $this->database->getReference($this->table . '/' . $key)->remove();
            }
        }

        return redirect()->route('dataAdmin.admin')->with('msg', 'Data berhasil dihapus');
    }
}
