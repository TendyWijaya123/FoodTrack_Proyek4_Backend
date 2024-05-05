<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Storage;

class ArtikelController extends Controller
{
    //
    private $database;
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->database = \App\Services\FirebaseService::connect();
        $this->storage = $storage;
    }


    public function create_artikel_makanan(Request $request)
    {
        $table_name = 'artikel_makanans';
        $id = $request->input('id');

        $existingArtikel = $this->database->getReference($table_name)
            ->orderByChild('id')
            ->equalTo($id)
            ->getSnapshot()->getValue();

        // Periksa apakah barcode sudah ada
        if (!empty($existingArtikel)) {
            // Jika sudah ada, kembalikan respons barcode sudah digunakan
            return response()->json(['message' => 'Barcode sudah digunakan'], 400);
        }
        $nama_artikel = $request->input('nama_artikel');
        $jenis = $request->input('jenis');

        $image = $request->file('image');
        $firebaseStoragePath = 'Images/ArtikelImage/';
        $fileName = $id . '_' . $image->getClientOriginalName();
        $fileContent = file_get_contents($image->getRealPath());
        $this->storage->getBucket()->upload($fileContent, [
            'name' => $firebaseStoragePath . $fileName,
        ]);

        $deskripsi = $request->input('deskripsi');
        $link = $request->input('link');



        // Gizi


        // Buat data makanan
        $makananData = [
            'id' => $id,
            'nama_artikel' => $nama_artikel,
            'jenis' => $jenis,
            'foto' => $fileName,
            'deskripsi' => $deskripsi,
            'link' => $link,


        ];

        // Simpan data ke database
        $postRef = $this->database->getReference($table_name)->getChild($id)->set($makananData);

        // Periksa apakah penyimpanan berhasil
        if ($postRef) {
            // Jika berhasil, kembalikan respon berhasil
            return response()->json(['message' => 'Data berhasil dipost'], 200);
        } else {
            // Jika gagal, kembalikan respon gagal
            return response()->json(['message' => 'Gagal memproses permintaan'], 500);
        }
    }
}
