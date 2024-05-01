<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Query\Filter\IsNull;

use Illuminate\Http\Request;

class adminController extends Controller
{
    protected $database;
    protected $table;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->table = "user";
    }

    public function tambah()
    {
        return view('/tambahAdmin', ['judul' => "Tambah Admin"]);
    }

    public function data()
    {
        $snapshot = $this->database->getReference($this->table)->orderByChild('role')->equalTo('admin')->getSnapshot();
        $data = $snapshot->getValue();

        // Kirim data ke view untuk ditampilkan
        return view('/dataAdmin', ['data' => $data, 'judul' => "Data Admin"]);
    }

    public function admin(Request $request)
    {
        $role = "admin";
        $username = $request->input('username');
        $email = $request->input('email');

        $usernameExists = $this->database->getReference($this->table)->orderByChild('username')->equalTo($username)->getSnapshot()->getValue();

        if (count($usernameExists) > 0) {
            return view('/tambahAdmin', ['msg' => "username admin sudah tersedia", 'judul' => "Tambah Admin"]);
        }


        $emailExists = $this->database->getReference($this->table)->orderByChild('email')->equalTo($email)->getSnapshot()->getValue();

        if (count($emailExists) > 0) {
            return view('/tambahAdmin', ['msg' => "email admin sudah tersedia", 'judul' => "Tambah Admin"]);
        }

        $postData = [
            'name' => $request->input("name"),
            'username' => $request->input("username"),
            'email' => $request->input("email"),
            'password' => Hash::make($request->input("password")),
            'role' => $role
        ];

        $postRef = $this->database->getReference($this->table)->push($postData);
        if ($postRef) {
            return view('/TambahAdmin', ['msg' => "berhasil mendaftarkan admin", 'judul' => "Data Admin"]);
        } else {
            return view('/TambahAdmin', ['msg' => " gagal didaftarkan ", 'judul' => "Tambah Admin"]);
        }
    }

    public function delete($username)
    {
        $username = "username_pengguna";
        $reference = $this->database->getReference($this->table)->orderByChild('username')->equalTo($username)->getSnapshot()->getValue();

        // Periksa apakah ada hasil yang sesuai dengan nama pengguna
        if (!empty($reference)) {
            foreach ($reference as $key => $value) {
                $this->database->getReference($this->table . '/' . $key)->remove();
            }
        }

        $snapshot = $this->database->getReference($this->table)->orderByChild('role')->equalTo('admin')->getSnapshot();
        $data = $snapshot->getValue();

        return redirect()->route('dataAdmin.admin')->with('msg', 'Data berhasil dihapus');
    }
}
