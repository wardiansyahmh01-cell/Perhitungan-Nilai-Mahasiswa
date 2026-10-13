<?php
/*
 * PRAKTIKUM PHP - PROGRAM PERHITUNGAN NILAI MAHASISWA (VERSI FORM)
 *
 * Ketentuan tugas yang dipenuhi:
 *   1. Minimal 4 variabel dengan tipe data berbeda -> bagian "VARIABEL"
 *   2. Minimal 1 percabangan                       -> if / elseif / else
 *   3. Minimal 1 perulangan                        -> for dan foreach
 *   4. Minimal 1 function buatan sendiri           -> bagian "FUNCTION"
 */

/* ==========================================================
   FUNCTION BUATAN SENDIRI
   ========================================================== */

// Menghitung nilai akhir dari nilai tiap komponen dan bobotnya (dalam persen)
function hitungNilaiAkhir(array $nilai, array $bobot): float
{
    $total = 0.0;
    foreach ($bobot as $komponen => $persen) {
        $total += $nilai[$komponen] * $persen / 100;
    }
    return round($total, 2);
}

// Menentukan grade (A-E) dengan mencocokkan nilai akhir ke skala nilai (percabangan if)
function tentukanGrade(float $nilaiAkhir, array $skala): array
{
    foreach ($skala as $baris) {
        if ($nilaiAkhir >= $baris["min"]) {
            return $baris;
        }
    }
    return end($skala); // cadangan: grade terendah
}

// Mengamankan teks sebelum ditampilkan di halaman
function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES, "UTF-8");
}

/* ==========================================================
   VARIABEL (tipe data berbeda)
   ========================================================== */
$nim         = "";     // string
$nama        = "";     // string
$fakultas    = "";     // string
$prodi       = "";     // string
$asalSekolah = "";     // string
$hobi        = "";     // string
$semester    = 0;      // integer
$batasLulus  = 55.0;   // float
$hasil       = false;  // boolean (true jika perhitungan berhasil)
$errors      = [];     // array: daftar pesan kesalahan

// array asosiatif: bobot penilaian (persen) dan label komponen
$bobot = ["tugas" => 20, "uts" => 25, "uas" => 25, "presensi" => 30];
$label = ["tugas" => "Tugas", "uts" => "UTS", "uas" => "UAS", "presensi" => "Presensi"];
$nilai = ["tugas" => "", "uts" => "", "uas" => "", "presensi" => ""];

// array: skala nilai 1-100 (diurutkan dari grade tertinggi)
$skala = [
    ["grade" => "A", "min" => 85, "max" => 100, "ket" => "Sangat Baik"],
    ["grade" => "B", "min" => 75, "max" => 84,  "ket" => "Baik"],
    ["grade" => "C", "min" => 65, "max" => 74,  "ket" => "Cukup"],
    ["grade" => "D", "min" => 55, "max" => 64,  "ket" => "Kurang"],
    ["grade" => "E", "min" => 1,  "max" => 54,  "ket" => "Sangat Kurang"],
];
$grade = "";   // grade yang diperoleh (A-E)

/* ==========================================================
   PROSES FORM
   ========================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nim         = trim($_POST["nim"] ?? "");
    $nama        = trim($_POST["nama"] ?? "");
    $fakultas    = trim($_POST["fakultas"] ?? "");
    $prodi       = trim($_POST["prodi"] ?? "");
    $asalSekolah = trim($_POST["asal_sekolah"] ?? "");
    $hobi        = trim($_POST["hobi"] ?? "");
    $semester    = (int) ($_POST["semester"] ?? 0);

    // Perulangan: mengambil nilai tiap komponen dari form
    foreach ($nilai as $kunci => $isi) {
        $nilai[$kunci] = trim($_POST[$kunci] ?? "");
    }

    // Validasi (percabangan)
    if ($nim === "" || !ctype_digit($nim)) {
        $errors[] = "NIM wajib diisi dan hanya berisi angka.";
    }
    if ($nama === "") {
        $errors[] = "Nama mahasiswa wajib diisi.";
    }
    if ($fakultas === "") {
        $errors[] = "Fakultas wajib diisi.";
    }
    if ($prodi === "") {
        $errors[] = "Program studi wajib diisi.";
    }
    if ($asalSekolah === "") {
        $errors[] = "Asal sekolah wajib diisi.";
    }
    if ($semester < 1 || $semester > 8) {
        $errors[] = "Pilih semester 1 sampai 8.";
    }
    foreach ($nilai as $kunci => $isi) {
        $valid = filter_var($isi, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 100]]);
        if ($valid === false) {
            $errors[] = "Nilai " . $label[$kunci] . " harus berupa bilangan bulat 1 sampai 100.";
        }
    }

    // Jika tidak ada kesalahan, lakukan perhitungan
    if (count($errors) === 0) {
        $nilaiAngka = [];
        foreach ($nilai as $kunci => $isi) {
            $nilaiAngka[$kunci] = (float) $isi;
        }

        $nilaiAkhir = hitungNilaiAkhir($nilaiAngka, $bobot);
        $infoGrade  = tentukanGrade($nilaiAkhir, $skala);
        $grade      = $infoGrade["grade"];
        $lulus      = $nilaiAkhir >= $batasLulus;
        $hasil      = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perhitungan Nilai Mahasiswa</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #08d3e5;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background-color: white;
            padding: 25px;
            border-radius: 10px;
        }

        h1 {
            text-align: center;
            color: #234f7d;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #0c75e7;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #2e7cc1;
        }

        .error {
            margin-top: 15px;
            padding: 12px 16px;
            background-color: #fdecea;
            color: #a52714;
            border-radius: 6px;
        }

        .error ul {
            margin: 5px 0 0;
            padding-left: 20px;
        }

        .hasil {
            margin-top: 30px;
            padding: 20px;
            background-color: #f1f6fc;
            border-radius: 8px;
        }

        .hasil h2 {
            margin-top: 0;
            color: #234f7d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: center;
            background-color: white;
        }

        th {
            background-color: #10466c;
            color: white;
        }

        .skala h2 {
            color: #234f7d;
            margin-bottom: 0;
        }

        .skala table {
            margin-top: 10px;
        }

        tr.aktif td {
            background-color: #fff3b0;
            font-weight: bold;
        }

        .lulus {
            color: #1e6b34;
            font-weight: bold;
        }

        .gagal {
            color: #b3241b;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Perhitungan Nilai Mahasiswa</h1>

    <form method="POST">

        <label>NIM</label>
        <input type="text" name="nim" value="<?= e($nim) ?>" required>

        <label>Nama Mahasiswa</label>
        <input type="text" name="nama" value="<?= e($nama) ?>" required>

        <label>Fakultas</label>
        <input type="text" name="fakultas" value="<?= e($fakultas) ?>" required>

        <label>Program Studi</label>
        <input type="text" name="prodi" value="<?= e($prodi) ?>" required>

        <label>Asal Sekolah</label>
        <input type="text" name="asal_sekolah" value="<?= e($asalSekolah) ?>" required>

        <label>Semester</label>
        <select name="semester" required>
            <option value="">-- Pilih Semester --</option>
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <option value="<?= $i ?>" <?= $semester === $i ? "selected" : "" ?>>Semester <?= $i ?></option>
            <?php endfor; ?>
        </select>

        <label>Hobi</label>
        <input type="text" name="hobi" value="<?= e($hobi) ?>">

        <?php foreach ($bobot as $kunci => $persen): ?>
            <label>Nilai <?= $label[$kunci] ?> (bobot <?= $persen ?>%)</label>
            <input type="number" name="<?= $kunci ?>" value="<?= e($nilai[$kunci]) ?>" min="1" max="100" step="1" placeholder="1 - 100" required>
        <?php endforeach; ?>

        <button type="submit">Hitung Nilai</button>

    </form>

    <div class="skala">
        <h2>Skala Nilai</h2>
        <table>
            <tr>
                <th>Grade</th>
                <th>Rentang Nilai</th>
                <th>Keterangan</th>
            </tr>
            <?php foreach ($skala as $baris): ?>
                <tr class="<?= $grade === $baris["grade"] ? "aktif" : "" ?>">
                    <td><?= $baris["grade"] ?></td>
                    <td><?= $baris["min"] ?> - <?= $baris["max"] ?></td>
                    <td><?= $baris["ket"] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php if (count($errors) > 0): ?>
        <div class="error">
            Mohon periksa kembali isian Anda:
            <ul>
                <?php foreach ($errors as $pesan): ?>
                    <li><?= e($pesan) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($hasil): ?>
        <div class="hasil">

            <h2>Hasil Perhitungan</h2>

            <p><strong>NIM:</strong> <?= e($nim) ?></p>
            <p><strong>Nama:</strong> <?= e($nama) ?></p>
            <p><strong>Fakultas:</strong> <?= e($fakultas) ?></p>
            <p><strong>Program Studi:</strong> <?= e($prodi) ?></p>
            <p><strong>Asal Sekolah:</strong> <?= e($asalSekolah) ?></p>
            <p><strong>Semester:</strong> <?= $semester ?></p>
            <p><strong>Hobi:</strong> <?= $hobi !== "" ? e($hobi) : "-" ?></p>

            <table>
                <tr>
                    <th>Komponen</th>
                    <th>Nilai</th>
                    <th>Bobot</th>
                    <th>Nilai x Bobot</th>
                </tr>
                <?php foreach ($bobot as $kunci => $persen): ?>
                    <tr>
                        <td><?= $label[$kunci] ?></td>
                        <td><?= number_format($nilaiAngka[$kunci], 2) ?></td>
                        <td><?= $persen ?>%</td>
                        <td><?= number_format($nilaiAngka[$kunci] * $persen / 100, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <p><strong>Nilai Akhir:</strong> <?= number_format($nilaiAkhir, 2) ?></p>
            <p><strong>Grade:</strong> <?= $grade ?> (<?= $infoGrade["ket"] ?>)</p>
            <p><strong>Status:</strong>
                <span class="<?= $lulus ? "lulus" : "gagal" ?>">
                    <?= $lulus ? "LULUS" : "TIDAK LULUS" ?>
                </span>
                (batas lulus <?= number_format($batasLulus, 0) ?>)
            </p>

        </div>
    <?php endif; ?>

</div>

</body>

</html>