<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Storage;

class MakananController extends Controller
{
    private $database;
    private $storage;
    protected $table;

    public function __construct(Storage $storage)
    {
        $this->database = \App\Services\FirebaseService::connect();
        $this->storage = $storage;
        $this->table = 'makanans';
    }


    public function assignGiziLabels(array $gizi): array
    {
        // Definisikan batas untuk setiap label
        $labels = [
            'rendah' => 'Rendah',
            'sedang_atau_tinggi' => 'Sedang atau Tinggi',
            'bebas' => 'Bebas',
        ];

        $thresholds = [
            'energi' => ['rendah' => 40, 'bebas' => 4],
            'lemak' => ['rendah' => 3, 'bebas' => 0.5],
            'gula' => ['rendah' => 5, 'bebas' => 0.5],
            'natrium' => ['rendah' => 120, 'bebas' => 5],
        ];

        function getLabel($value, $thresholds, $labels)
        {
            // Prioritaskan label 'sedang_atau_tinggi' dan 'rendah'
            if (isset($thresholds['bebas']) && $value <= $thresholds['bebas']) {
                return $labels['bebas'];
            } elseif (isset($thresholds['rendah']) && $value > $thresholds['rendah']) {
                return $labels['sedang_atau_tinggi'];
            } else {
                // Default ke label 'rendah' jika tidak memenuhi syarat lain
                return $labels['rendah'];
            }
        }

        $labeledGizi = [];
        foreach ($gizi as $komponen => $nilai) {
            if (isset($thresholds[$komponen])) {
                $labeledGizi[$komponen . '_label'] = getLabel($nilai, $thresholds[$komponen], $labels);
            }
        }

        return $labeledGizi;
    }




    public function create_makanan(Request $request)
    {
        // dd($request->input());
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
            'energi' => $request->input('energi'),
            'gula' => $request->input('gula')
        ];
        try {
            $labeledGizi = $this->assignGiziLabels($gizi);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat memberi label gizi: ' . $e->getMessage()], 500);
        }

        // Buat data makanan
        $makananData = [
            'barcode' => $barcode,
            'nama_makanan' => $nama_makanan,
            'jenis' => $jenis,
            'foto' => $fileName,
            'gizi' => $gizi,
            'takaran' => $request->input('takaran_per_saji'),
            'label_gizi' => $labeledGizi,
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

    public function readMakanan()
    {
        $dataMakanan = $this->database->getReference($this->table)->getValue();
        return response()->json($dataMakanan, 200);
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

    public function search_makanan_by_jenis(Request $request)
    {
        $table_name = 'makanans';
        $jenis = $request->input('jenis');

        try {
            $query = $this->database->getReference($table_name)
                ->orderByChild('jenis')
                ->equalTo($jenis)
                ->getSnapshot();

            $results = [];
            foreach ($query->getValue() as $barcode => $makanan) {
                $makanan['barcode'] = $barcode;
                $results[] = $makanan;
            }

            if (!empty($results)) {
                // Shuffle the results array to randomize the order
                shuffle($results);

                // Select up to two random instances
                $randomResults = array_slice($results, 0, 2);

                return response()->json(['message' => 'Data ditemukan', 'data' => $randomResults], 200);
            } else {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }



    public function search_makanan_barcode(Request $request)
    {
        $table_name = 'makanans';
        $keyword = $request->input('keyword');
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

    public function update_makanan(Request $request, $barcode)
    {
        // dd($request, $barcode);
        $table_name = 'makanans';

        $existingMakanan = $this->database->getReference($table_name)
            ->orderByChild('barcode')
            ->equalTo($barcode)
            ->getSnapshot()->getValue();

        // Periksa apakah makanan dengan barcode tersebut ada
        if (empty($existingMakanan)) {
            return response()->json(['message' => 'Makanan tidak ditemukan'], 404);
        }

        // Dapatkan kunci dari item yang ada
        $key = array_key_first($existingMakanan);

        $nama_makanan = $request->input('nama_makanan');
        $jenis = $request->input('jenis');

        $makananData = [
            'barcode' => $barcode,
            'nama_makanan' => $nama_makanan,
            'jenis' => $jenis,
            'takaran' => $request->input('takaran_per_saji'),
            // Tambahkan data lain sesuai kebutuhan
        ];

        // Tetapkan foto lama jika ada, atau kosong jika tidak ada
        $makananData['foto'] = $existingMakanan[$key]['foto'] ?? '';

        // Gizi
        $gizi = [
            'protein' => $request->input('protein') ?? $existingMakanan[$key]['gizi']['protein'],
            'karbohidrat' => $request->input('karbohidrat') ?? $existingMakanan[$key]['gizi']['karbohidrat'],
            'lemak' => $request->input('lemak') ?? $existingMakanan[$key]['gizi']['lemak'],
            'kalori' => $request->input('kalori') ?? $existingMakanan[$key]['gizi']['kalori'],
            'natrium' => $request->input('natrium') ?? $existingMakanan[$key]['gizi']['natrium'],
            'vitamin_a' => $request->input('vitamin_a') ?? $existingMakanan[$key]['gizi']['vitamin_a'],
            'vitamin_b1' => $request->input('vitamin_b1') ?? $existingMakanan[$key]['gizi']['vitamin_b1'],
            'vitamin_b2' => $request->input('vitamin_b2') ?? $existingMakanan[$key]['gizi']['vitamin_b2'],
            'vitamin_b3' => $request->input('vitamin_b3') ?? $existingMakanan[$key]['gizi']['vitamin_b3'],
            'vitamin_c' => $request->input('vitamin_c') ?? $existingMakanan[$key]['gizi']['vitamin_c'],
            'serat' => $request->input('serat') ?? $existingMakanan[$key]['gizi']['serat'],
            'energi' => $request->input('energi') ?? $existingMakanan[$key]['gizi']['energi'],
            'gula' => $request->input('gula') ?? $existingMakanan[$key]['gizi']['gula'],
        ];

        $makananData['gizi'] = $gizi;

        // Update data ke database
        $updateRef = $this->database->getReference($table_name)->getChild($key)->update($makananData);

        // Periksa apakah pembaruan berhasil
        if ($updateRef) {
            return response()->json(['message' => 'Data berhasil diperbarui'], 200);
        } else {
            return response()->json(['message' => 'Gagal memperbarui data'], 500);
        }
    }


    public function deleteMakanan(Request $request)
    {
        $table_name = 'makanans';
        $barcode = $request->input('id');

        // Cari makanan berdasarkan barcode
        $makananRef = $this->database->getReference($table_name)->getChild($barcode);

        // Periksa apakah makanan ada
        if ($makananRef->getSnapshot()->exists()) {
            // Dapatkan data makanan
            $makananData = $makananRef->getValue();

            // Hapus gambar dari Firebase Storage jika ada
            if (isset($makananData['foto'])) {
                $fileName = $makananData['foto'];
                $firebaseStoragePath = 'Images/MakananImage/';
                $filePath = $firebaseStoragePath . $fileName;

                // Hapus file dari storage
                $this->storage->getBucket()->object($filePath)->delete();
            }

            // Hapus data dari database
            $makananRef->remove();

            // Kembalikan respon berhasil
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } else {
            // Kembalikan respon tidak ditemukan
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }

    public function rekomendasi_makanan(Request $request)
    {
        $email = $request->input('email');

        // Ambil data profil dan kebutuhan gizi
        $profile = $this->database->getReference('data_profile')
            ->orderByChild('email')
            ->equalTo($email)
            ->getSnapshot()
            ->getValue();

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $profile = array_values($profile)[0];
        $kebutuhanGizi = $this->database->getReference('data_kebutuhan_gizi')
            ->orderByChild('email')
            ->equalTo($email)
            ->getSnapshot()
            ->getValue();

        if (!$kebutuhanGizi) {
            return response()->json(['message' => 'Nutritional needs not found'], 404);
        }

        $kebutuhanGizi = array_values($kebutuhanGizi)[0];

        // Daftar makanan dengan kandungan gizi per porsi
        $makanan = $this->readMakanan()->getData(true);

        $rekomendasi = [];

        foreach ($makanan as $item) {
            $gizi = $item['gizi'];
            // dd($gizi['kalori']);
            if (isset($gizi['kalori'], $gizi['protein'], $gizi['lemak'], $gizi['karbohidrat'])) {
                if (
                    $gizi['kalori'] <= $kebutuhanGizi['kalori'] * 0.2 &&
                    $gizi['protein'] <= $kebutuhanGizi['protein'] * 0.2 &&
                    $gizi['lemak'] <= $kebutuhanGizi['lemak'] * 0.2 &&
                    $gizi['karbohidrat'] <= $kebutuhanGizi['karbohidrat'] * 0.2
                ) {
                    $rekomendasi[] = $item;
                }
            } else {
                continue;
            }
        }

        // Mengurutkan makanan berdasarkan kecocokan (opsional)
        usort($rekomendasi, function ($a, $b) use ($kebutuhanGizi) {
            return abs($a['kalori'] - $kebutuhanGizi['kalori']) <=> abs($b['kalori'] - $kebutuhanGizi['kalori']);
        });

        return response()->json(['message' => 'rekomendasi tersedia', 'data' => $rekomendasi], 200);
    }
}
