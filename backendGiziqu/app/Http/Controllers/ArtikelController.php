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

        // Retrieve all existing IDs
        $allIdsSnapshot = $this->database->getReference($table_name)->getSnapshot()->getValue();

        $allIds = [];
        if ($allIdsSnapshot) {
            foreach ($allIdsSnapshot as $key => $value) {
                $allIds[] = (int) $key;
            }
        }

        // Sort the IDs to find the highest one
        sort($allIds);

        // Generate the next ID
        $nextId = !empty($allIds) ? end($allIds) + 1 : 1;
        $id = (string) $nextId;

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

        if ($postRef) {
            return response()->json(['message' => 'Data berhasil dipost'], 200);
        } else {
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

    public function deleteArtikel(Request $request)
    {
        $table_name = 'artikel_makanans';
        $id = $request->input('id');

        // Cari makanan berdasarkan barcode
        $artikelRef = $this->database->getReference($table_name)->getChild($id);

        // Periksa apakah makanan ada
        if ($artikelRef->getSnapshot()->exists()) {
            // Dapatkan data makanan
            $artikelData = $artikelRef->getValue();

            // Hapus gambar dari Firebase Storage jika ada
            if (isset($artikelData['foto'])) {
                $fileName = $artikelData['foto'];
                $firebaseStoragePath = 'Images/ArtikelImage/';
                $filePath = $firebaseStoragePath . $fileName;

                // Hapus file dari storage
                $this->storage->getBucket()->object($filePath)->delete();
            }

            // Hapus data dari database
            $artikelRef->remove();

            // Kembalikan respon berhasil
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } else {
            // Kembalikan respon tidak ditemukan
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }
}
