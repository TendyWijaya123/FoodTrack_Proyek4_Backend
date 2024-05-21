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

    public function read_semua_artikel_makanan()
    {
        $table_name = 'artikel_makanans';

        // Ambil semua data dari tabel artikel makanan
        $artikelMakananRef = $this->database->getReference($table_name)->getValue();

        // Periksa apakah ada data yang ditemukan
        if (!empty($artikelMakananRef)) {
            // Jika ditemukan, kembalikan data artikel makanan
            return response()->json($artikelMakananRef, 200);
        } else {
            // Jika tidak ditemukan, kembalikan respons bahwa tidak ada data
            return response()->json(['message' => 'Tidak ada data artikel makanan'], 404);
        }
    }



    public function update_artikel_makanan(Request $request)
    {
        $table_name = 'artikel_makanans';
        $id = $request->input('id');

        // Periksa apakah artikel makanan dengan ID yang diberikan ada
        $existingArtikel = $this->database->getReference($table_name)
            ->orderByChild('id')
            ->equalTo($id)
            ->getSnapshot()->getValue();

        if (empty($existingArtikel)) {
            // Jika tidak ada artikel makanan dengan ID yang diberikan, kembalikan respons bahwa ID tidak ditemukan
            return response()->json(['message' => 'ID tidak ditemukan'], 404);
        }

        // Lakukan pembaruan data artikel makanan
        $nama_artikel = $request->input('nama_artikel');
        $jenis = $request->input('jenis');
        $deskripsi = $request->input('deskripsi');
        $link = $request->input('link');

        $makananData = [
            'nama_artikel' => $nama_artikel,
            'jenis' => $jenis,
            'deskripsi' => $deskripsi,
            'link' => $link,
        ];

        // Periksa apakah ada permintaan untuk mengganti foto
        if ($request->hasFile('image')) {
            // Jika ada, proses pembaruan foto
            $image = $request->file('image');
            $firebaseStoragePath = 'Images/ArtikelImage/';
            $fileName = $id . '_' . $image->getClientOriginalName();
            $fileContent = file_get_contents($image->getRealPath());
            $this->storage->getBucket()->upload($fileContent, [
                'name' => $firebaseStoragePath . $fileName,
            ]);
            // Tambahkan nama file gambar ke data makanan yang akan diperbarui
            $makananData['foto'] = $fileName;
        }

        // Lakukan pembaruan di database
        $updateRef = $this->database->getReference($table_name)->getChild($id)->update($makananData);

        // Periksa apakah pembaruan berhasil
        if ($updateRef) {
            // Jika berhasil, kembalikan respons berhasil
            return response()->json(['message' => 'Data berhasil diperbarui'], 200);
        } else {
            // Jika gagal, kembalikan respons gagal
            return response()->json(['message' => 'Gagal memperbarui data'], 500);
        }
    }



    public function delete_artikel_makanan(Request $request)
    {
        $table_name = 'artikel_makanans';
        $id = $request->input('id');

        // Hapus artikel makanan berdasarkan ID
        $deleteRef = $this->database->getReference($table_name)->getChild($id)->remove();

        // Periksa apakah penghapusan berhasil
        if ($deleteRef) {
            // Jika berhasil, kembalikan respons berhasil
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } else {
            // Jika gagal, kembalikan respons gagal
            return response()->json(['message' => 'Gagal menghapus data'], 500);
        }
    }
}
