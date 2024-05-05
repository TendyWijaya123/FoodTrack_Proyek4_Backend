<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Storage;

class MakananController extends Controller
{
    //
    //
    private $database;
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->database = \App\Services\FirebaseService::connect();
        $this->storage = $storage;
    }

    public function create_makanan(Request $request)
    {
        $table_name = 'makanans';
        $barcode = $request->input('barcode');

        $existingMakanan = $this->database->getReference($table_name)
            ->orderByChild('barcode')
            ->equalTo($barcode)
            ->getSnapshot()->getValue();

        // Periksa apakah barcode sudah ada
        if (!empty($existingMakanan)) {
            // Jika sudah ada, kembalikan respons barcode sudah digunakan
            return response()->json(['message' => 'Barcode sudah digunakan'], 400);
        }
        $nama_makanan = $request->input('nama_makanan');
        $jenis = $request->input('jenis');
        // $foto = $request->input('foto');
        $image = $request->file('image');
        $firebaseStoragePath = 'Images/MakananImage/';
        $fileName = $barcode . '_' . $image->getClientOriginalName();
        $fileContent = file_get_contents($image->getRealPath());
        $this->storage->getBucket()->upload($fileContent, [
            'name' => $firebaseStoragePath . $fileName,
        ]);


        // Gizi
        $gizi = [
            'protein' => $request->input('protein'),
            'karbohidrat' => $request->input('karbohidrat'),
            'lemak' => $request->input('lemak'),
            'kalori' => $request->input('kalori'),
            'natrium' => $request->input('natrium'),
            'vitamin_a' => $request->input('vitamin_a'),
            'vitamin_b1' => $request->input('vitamin_b1'),
            'vitamin_b2' => $request->input('vitamin_b2'),
            'vitamin_b3' => $request->input('vitamin_b3'),
            'vitamin_c' => $request->input('vitamin_c'),
            'serat' => $request->input('serat'),
        ];

        // Buat data makanan
        $makananData = [
            'barcode' => $barcode,
            'nama_makanan' => $nama_makanan,
            'jenis' => $jenis,
            'foto' => $fileName,
            'gizi' => $gizi,
        ];

        // Simpan data ke database
        $postRef = $this->database->getReference($table_name)->getChild($barcode)->set($makananData);

        // Periksa apakah penyimpanan berhasil
        if ($postRef) {
            // Jika berhasil, kembalikan respon berhasil
            return response()->json(['message' => 'Data berhasil dipost'], 200);
        } else {
            // Jika gagal, kembalikan respon gagal
            return response()->json(['message' => 'Gagal memproses permintaan'], 500);
        }
    }

    public function search_makanan(Request $request)
    {
        $table_name = 'makanans';
        $keyword = $request->input('keyword');

        // Lakukan pencarian berdasarkan nama makanan atau barcode
        $query = $this->database->getReference($table_name)
            ->orderByChild('nama_makanan')
            ->startAt($keyword)
            ->endAt($keyword . "\uf8ff")
            ->getSnapshot();

        $results = [];

        // Ambil hasil pencarian
        foreach ($query->getValue() as $barcode => $makanan) {
            if ($barcode === $keyword || stripos($makanan['nama_makanan'], $keyword) !== false) {
                $makanan['barcode'] = $barcode;
                $results[] = $makanan;
            }
        }

        // Periksa apakah ada hasil pencarian
        if (!empty($results)) {
            // Jika ada hasil, kembalikan respons berhasil
            return response()->json(['message' => 'Data ditemukan', 'data' => $results], 200);
        } else {
            // Jika tidak ada hasil, kembalikan respons tidak ditemukan
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }

    public function search_makanan_barcode(Request $request)
    {
        $table_name = 'makanans';
        $keyword = intval($request->input('keyword'));
        // Pastikan $keyword tidak null
        if ($keyword === null) {
            return response()->json(['message' => 'Keyword tidak diberikan'], 400);
        }

        // Lakukan pencarian berdasarkan nama makanan atau barcode
        $query = $this->database->getReference($table_name)
            ->orderByChild('barcode')
            ->startAt($keyword)
            ->endAt($keyword . "\uf8ff")
            ->getSnapshot();
        $queryValue = $query->getValue();

        $results = [];

        foreach ($queryValue as $barcode => $makanan) {
            if ($barcode === $keyword || stripos($makanan['nama_makanan'], $keyword) !== false) {
                $makanan['barcode'] = $barcode;
                $results[] = $makanan;
            }
        }
        // Periksa apakah ada hasil pencarian
        if (!empty($results)) {
            return response()->json(['message' => 'Data ditemukan', 'data' => $results], 200);
        } else {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }
}
