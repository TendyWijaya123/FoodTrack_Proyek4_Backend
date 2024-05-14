<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    //

    private $database;

    public function __construct()
    {
        $this->database = \App\Services\FirebaseService::connect();
    }


    // public function create_update_profile_diri(Request $request)
    // {
    //     $table_name = 'data_profile';
    //     $email = $request->input('email');
    //     $table_name_gizi = 'data_kebutuhan_gizi';


    //     $existingProfile = $this->database->getReference($table_name)
    //         ->orderByChild('email')
    //         ->equalTo($email)
    //         ->getSnapshot()->getValue();

    //     $tinggi_badan = $request->input('tinggi_badan');
    //     $berat_badan = $request->input('berat_badan');
    //     $jenis_kelamin = $request->input('jenis_kelamin');
    //     $usia = $request->input('usia');
    //     $faktor_aktivitas = $request->input('faktor_aktivitas');



    //     $profileData = [
    //         'email' => $email,
    //         'tinggi_badan' => $tinggi_badan,
    //         'berat_badan' => $berat_badan,
    //         'jenis_kelamin' => $jenis_kelamin,
    //         'usia' => $usia,
    //         'faktor_aktivitas' => $faktor_aktivitas,
    //     ];

    //     $kalori = 0;
    //     $protein = 0;
    //     $lemak = 0;
    //     $karbohidrat = 0;

    //     if ($faktor_aktivitas == "Sangat jarang berolahraga") {
    //         if ($jenis_kelamin == "laki-laki") {
    //             $kalori = 66 + (13.7 * $berat_badan) + (5 * $tinggi_badan) - (6.8 * $usia);
    //             $kalori = $kalori * 1.2;
    //         } else {
    //             $kalori = 655 + (9.6 * $berat_badan) + (1.8 * $tinggi_badan) - (4.7 * $usia);
    //             $kalori = $kalori * 1.2;
    //         }
    //     } elseif ($faktor_aktivitas == "Jarang olahraga") {
    //         if ($jenis_kelamin == "laki-laki") {
    //             $kalori = 66 + (13.7 * $berat_badan) + (5 * $tinggi_badan) - (6.8 * $usia);
    //             $kalori = $kalori * 1.375;
    //         } else {
    //             $kalori = 655 + (9.6 * $berat_badan) + (1.8 * $tinggi_badan) - (4.7 * $usia);
    //             $kalori = $kalori * 1.375;
    //         }
    //     } elseif ($faktor_aktivitas == "Cukup olahraga") {
    //         if ($jenis_kelamin == "laki-laki") {
    //             $kalori = 66 + (13.7 * $berat_badan) + (5 * $tinggi_badan) - (6.8 * $usia);
    //             $kalori = $kalori * 1.55;
    //         } else {
    //             $kalori = 655 + (9.6 * $berat_badan) + (1.8 * $tinggi_badan) - (4.7 * $usia);
    //             $kalori = $kalori * 1.55;
    //         }
    //     } elseif ($faktor_aktivitas == "Sering olahraga") {
    //         if ($jenis_kelamin == "laki-laki") {
    //             $kalori = 66 + (13.7 * $berat_badan) + (5 * $tinggi_badan) - (6.8 * $usia);
    //             $kalori = $kalori * 1.725;
    //         } else {
    //             $kalori = 655 + (9.6 * $berat_badan) + (1.8 * $tinggi_badan) - (4.7 * $usia);
    //             $kalori = $kalori * 1.725;
    //         }
    //     } elseif ($faktor_aktivitas == "Sangat sering olahraga") {
    //         if ($jenis_kelamin == "laki-laki") {
    //             $kalori = 66 + (13.7 * $berat_badan) + (5 * $tinggi_badan) - (6.8 * $usia);
    //             $kalori = $kalori * 1.9;
    //         } else {
    //             $kalori = 655 + (9.6 * $berat_badan) + (1.8 * $tinggi_badan) - (4.7 * $usia);
    //             $kalori = $kalori * 1.9;
    //         }
    //     }

    //     $protein = 0.15 * $kalori / 4;
    //     $karbohidrat = $kalori * 0.6 / 4;
    //     $lemak = $kalori * 0.15 / 4;




    //     $kebutuhanGiziData = [
    //         'email' => $email,
    //         'kalori' => $kalori,
    //         'karbohidrat' => $karbohidrat,
    //         'protein' => $protein,
    //         'lemak' => $lemak,


    //     ];

    //     // Simpan data ke database


    //     if (empty($existingProfile)) {
    //         // Jika sudah ada maka dilakukan update
    //         $postRef = $this->database->getReference($table_name)->push()->set($profileData);
    //         $postRef = $this->database->getReference($table_name_gizi)->push()->set($kebutuhanGiziData);;
    //     } else {
    //         $postRef = $this->database->getReference($table_name)->set($profileData);
    //         $postRef = $this->database->getReference($table_name_gizi)->set($kebutuhanGiziData);
    //     }

    //     // Periksa apakah penyimpanan berhasil
    //     if ($postRef) {
    //         return response()->json(['message' => 'Data berhasil disimpan'], 200);
    //     } else {
    //         return response()->json(['message' => 'Gagal memproses permintaan'], 500);
    //     }
    // }

    public function create_update_profile_diri(Request $request)
    {
        $table_name = 'data_profile';
        $email = $request->input('email');
        $table_name_gizi = 'data_kebutuhan_gizi';

        $existingProfile = $this->database->getReference($table_name)
            ->orderByChild('email')
            ->equalTo($email)
            ->getSnapshot()->getValue();

        $tinggi_badan = $request->input('tinggi_badan');
        $berat_badan = $request->input('berat_badan');
        $jenis_kelamin = $request->input('jenis_kelamin');
        $usia = $request->input('usia');
        $faktor_aktivitas = $request->input('faktor_aktivitas');

        $profileData = [
            'email' => $email,
            'tinggi_badan' => $tinggi_badan,
            'berat_badan' => $berat_badan,
            'jenis_kelamin' => $jenis_kelamin,
            'usia' => $usia,
            'faktor_aktivitas' => $faktor_aktivitas,
        ];

        // Hitung kalori dan data kebutuhan gizi lainnya
        $kalori = 0;
        $protein = 0;
        $lemak = 0;
        $karbohidrat = 0;

        // Kalkulasi kalori, protein, karbohidrat, dan lemak
        // ...

        if ($faktor_aktivitas == "Sangat jarang berolahraga") {
            if ($jenis_kelamin == "laki-laki") {
                $kalori = 66 + (13.7 * $berat_badan) + (5 * $tinggi_badan) - (6.8 * $usia);
                $kalori = $kalori * 1.2;
            } else {
                $kalori = 655 + (9.6 * $berat_badan) + (1.8 * $tinggi_badan) - (4.7 * $usia);
                $kalori = $kalori * 1.2;
            }
        } elseif ($faktor_aktivitas == "Jarang olahraga") {
            if ($jenis_kelamin == "laki-laki") {
                $kalori = 66 + (13.7 * $berat_badan) + (5 * $tinggi_badan) - (6.8 * $usia);
                $kalori = $kalori * 1.375;
            } else {
                $kalori = 655 + (9.6 * $berat_badan) + (1.8 * $tinggi_badan) - (4.7 * $usia);
                $kalori = $kalori * 1.375;
            }
        } elseif ($faktor_aktivitas == "Cukup olahraga") {
            if ($jenis_kelamin == "laki-laki") {
                $kalori = 66 + (13.7 * $berat_badan) + (5 * $tinggi_badan) - (6.8 * $usia);
                $kalori = $kalori * 1.55;
            } else {
                $kalori = 655 + (9.6 * $berat_badan) + (1.8 * $tinggi_badan) - (4.7 * $usia);
                $kalori = $kalori * 1.55;
            }
        } elseif ($faktor_aktivitas == "Sering olahraga") {
            if ($jenis_kelamin == "laki-laki") {
                $kalori = 66 + (13.7 * $berat_badan) + (5 * $tinggi_badan) - (6.8 * $usia);
                $kalori = $kalori * 1.725;
            } else {
                $kalori = 655 + (9.6 * $berat_badan) + (1.8 * $tinggi_badan) - (4.7 * $usia);
                $kalori = $kalori * 1.725;
            }
        } elseif ($faktor_aktivitas == "Sangat sering olahraga") {
            if ($jenis_kelamin == "laki-laki") {
                $kalori = 66 + (13.7 * $berat_badan) + (5 * $tinggi_badan) - (6.8 * $usia);
                $kalori = $kalori * 1.9;
            } else {
                $kalori = 655 + (9.6 * $berat_badan) + (1.8 * $tinggi_badan) - (4.7 * $usia);
                $kalori = $kalori * 1.9;
            }
        }

        $protein = 0.15 * $kalori / 4;
        $karbohidrat = $kalori * 0.6 / 4;
        $lemak = $kalori * 0.15 / 4;

        $kebutuhanGiziData = [
            'email' => $email,
            'kalori' => $kalori,
            'karbohidrat' => $karbohidrat,
            'protein' => $protein,
            'lemak' => $lemak,
        ];

        // Dapatkan kunci unik dari profil yang ingin diperbarui
        $profileKey = array_key_first($existingProfile);

        if (!empty($existingProfile)) {
            // Jika profil sudah ada, lakukan pembaruan
            $this->database->getReference("$table_name/$profileKey")->update($profileData);
            $this->database->getReference("$table_name_gizi/$profileKey")->update($kebutuhanGiziData);
        } else {
            // Jika profil belum ada, buat profil baru
            $profileRef = $this->database->getReference($table_name)->push()->getKey();
            $this->database->getReference("$table_name/$profileRef")->set($profileData);
            $this->database->getReference("$table_name_gizi/$profileRef")->set($kebutuhanGiziData);
        }

        // Beri respons
        return response()->json(['message' => 'Data berhasil disimpan/diperbarui'], 200);
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
