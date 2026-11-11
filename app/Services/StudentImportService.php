<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\UploadedFile;
use ZipArchive;

class StudentImportService
{
    /**
     * Parse and import students from CSV or XLSX file.
     *
     * @return array{total: int, imported: int, updated: int, skipped: int, errors: array<int, string>}
     */
    public function import(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if (in_array($extension, ['csv', 'txt'])) {
            $rows = $this->parseCsv($file->getRealPath());
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            $rows = $this->parseXlsx($file->getRealPath());
        } else {
            throw new \InvalidArgumentException('Format file tidak didukung. Harap unggah file .csv atau .xlsx.');
        }

        if (empty($rows)) {
            return [
                'total' => 0,
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => ['File kosong atau format kolom tidak terbaca.'],
            ];
        }

        return $this->processRows($rows);
    }

    /**
     * Parse CSV file with delimiter auto-detection.
     *
     * @return array<int, array<string, string>>
     */
    protected function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        // Baca baris pertama untuk mendeteksi delimiter
        $firstLine = fgets($handle);
        if (! $firstLine) {
            fclose($handle);
            return [];
        }

        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        rewind($handle);

        $headers = [];
        $rows = [];
        $lineNumber = 0;

        while (($data = fgetcsv($handle, 2048, $delimiter)) !== false) {
            $lineNumber++;
            if ($lineNumber === 1) {
                $headers = array_map(fn ($h) => $this->normalizeHeader((string) $h), $data);
                continue;
            }

            if (empty(array_filter($data, fn ($val) => trim((string) $val) !== ''))) {
                continue; // baris kosong
            }

            $row = [];
            foreach ($headers as $index => $key) {
                if ($key !== '' && isset($data[$index])) {
                    $row[$key] = trim((string) $data[$index]);
                }
            }

            if (! empty($row)) {
                $rows[] = $row;
            }
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Parse XLSX file natively using ZipArchive & SimpleXML.
     *
     * @return array<int, array<string, string>>
     */
    protected function parseXlsx(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            // Jika bukan arsip zip valid, coba fallback baca sebagai CSV
            return $this->parseCsv($path);
        }

        // 1. Ekstrak shared strings jika ada
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $xml = simplexml_load_string($sharedStringsXml);
            if ($xml) {
                foreach ($xml->si as $val) {
                    if (isset($val->t)) {
                        $sharedStrings[] = (string) $val->t;
                    } elseif (isset($val->r)) {
                        $tParts = [];
                        foreach ($val->r as $r) {
                            $tParts[] = (string) $r->t;
                        }
                        $sharedStrings[] = implode('', $tParts);
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Baca sheet1.xml
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            // Coba periksa file sheet lain
            for ($i = 1; $i <= 5; $i++) {
                $sheetXml = $zip->getFromName("xl/worksheets/sheet{$i}.xml");
                if ($sheetXml !== false) {
                    break;
                }
            }
        }

        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        $xml = simplexml_load_string($sheetXml);
        if (! $xml || ! isset($xml->sheetData->row)) {
            return [];
        }

        $rawGrid = [];
        foreach ($xml->sheetData->row as $rowNode) {
            $rowIndex = (int) $rowNode['r'];
            $rowData = [];

            foreach ($rowNode->c as $cell) {
                $cellRef = (string) $cell['r']; // e.g. A1, B2
                $colLetters = preg_replace('/[0-9]/', '', $cellRef);
                $colIndex = $this->columnLetterToIndex($colLetters);

                $cellType = (string) $cell['t'];
                $val = isset($cell->v) ? (string) $cell->v : '';

                if ($cellType === 's' && isset($sharedStrings[(int) $val])) {
                    $val = $sharedStrings[(int) $val];
                }

                $rowData[$colIndex] = trim($val);
            }

            if (! empty($rowData)) {
                $rawGrid[$rowIndex] = $rowData;
            }
        }

        if (empty($rawGrid)) {
            return [];
        }

        // Baris pertama sebagai header
        $headerRow = array_shift($rawGrid);
        $headers = [];
        foreach ($headerRow as $colIdx => $title) {
            $headers[$colIdx] = $this->normalizeHeader($title);
        }

        $parsedRows = [];
        foreach ($rawGrid as $cells) {
            $row = [];
            foreach ($headers as $colIdx => $key) {
                if ($key !== '' && isset($cells[$colIdx])) {
                    $row[$key] = $cells[$colIdx];
                }
            }

            if (! empty(array_filter($row, fn ($v) => trim($v) !== ''))) {
                $parsedRows[] = $row;
            }
        }

        return $parsedRows;
    }

    /**
     * Convert Excel column letters (A, B, AA) to 0-based integer index.
     */
    protected function columnLetterToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $len = strlen($letters);
        $index = 0;
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }
        return $index - 1;
    }

    /**
     * Normalize header name to match student attributes.
     */
    protected function normalizeHeader(string $header): string
    {
        $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $header)));

        if (in_array($clean, ['nim', 'nomorinduk', 'nomorindukmahasiswa', 'studentid', 'npm'])) {
            return 'nim';
        }

        if (in_array($clean, ['name', 'nama', 'namalengkap', 'namamahasiswa', 'fullname', 'studentname'])) {
            return 'name';
        }

        if (in_array($clean, ['studyprogram', 'prodi', 'programstudi', 'jurusan', 'major', 'fakultasjurusan'])) {
            return 'study_program';
        }

        if (in_array($clean, ['whatsappnumber', 'whatsapp', 'wa', 'nowa', 'nomorwa', 'phone', 'telepon', 'nohp', 'nohandphone', 'kontak'])) {
            return 'whatsapp_number';
        }

        if (in_array($clean, ['semester', 'sem', 'tingkat', 'smt'])) {
            return 'semester';
        }

        if (in_array($clean, ['regular', 'reguler', 'kelas', 'shift', 'jeniskelas', 'tipekelas'])) {
            return 'regular_class';
        }

        if (in_array($clean, ['notes', 'catatan', 'keterangan', 'ket'])) {
            return 'notes';
        }

        return '';
    }

    /**
     * Process parsed rows and upsert students into database.
     *
     * @param array<int, array<string, string>> $rows
     * @return array{total: int, imported: int, updated: int, skipped: int, errors: array<int, string>}
     */
    protected function processRows(array $rows): array
    {
        $total = count($rows);
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        $allPrograms = Student::STUDY_PROGRAMS;

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Baris spreadsheet (header = 1)

            $rawNim = $row['nim'] ?? '';
            $nimDigits = preg_replace('/[^0-9]/', '', $rawNim);

            // Validasi NIM 12 digit
            if (strlen($nimDigits) !== 12) {
                // Coba auto-pad leading zero jika angka 10-11 digit
                if (strlen($nimDigits) >= 10 && strlen($nimDigits) < 12) {
                    $nimDigits = str_pad($nimDigits, 12, '0', STR_PAD_LEFT);
                } else {
                    $errors[] = "Baris {$rowNum}: NIM '{$rawNim}' tidak valid (wajib 12 digit angka).";
                    $skipped++;
                    continue;
                }
            }

            $name = trim($row['name'] ?? '');
            if ($name === '') {
                $errors[] = "Baris {$rowNum}: Nama mahasiswa kosong.";
                $skipped++;
                continue;
            }

            $rawProdi = trim($row['study_program'] ?? '');
            $matchedProdi = $this->matchStudyProgram($rawProdi, $allPrograms);

            $rawPhone = trim($row['whatsapp_number'] ?? '');
            $phone = Student::normalizePhone($rawPhone);
            if ($phone === '') {
                $phone = '628' . substr($nimDigits, -9); // Fallback dummy phone jika kosong
            }

            $semester = (int) ($row['semester'] ?? 1);
            if ($semester < 1 || $semester > 14) {
                $semester = 1;
            }

            $rawReg = trim($row['regular_class'] ?? '');
            $regularClass = $this->matchRegularClass($rawReg);

            $notes = trim($row['notes'] ?? 'Diimpor massal');

            $existing = Student::where('nim', $nimDigits)->first();

            if ($existing) {
                $existing->update([
                    'name' => $name,
                    'study_program' => $matchedProdi,
                    'regular_class' => $regularClass,
                    'semester' => $semester,
                    'whatsapp_number' => $phone,
                    'is_active' => true,
                    'notes' => $notes,
                ]);
                $updated++;
            } else {
                Student::create([
                    'nim' => $nimDigits,
                    'name' => $name,
                    'study_program' => $matchedProdi,
                    'regular_class' => $regularClass,
                    'semester' => $semester,
                    'whatsapp_number' => $phone,
                    'is_active' => true,
                    'notes' => $notes,
                ]);
                $imported++;
            }
        }

        return [
            'total' => $total,
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Fuzzy matching for study program string to official UNPAM programs.
     *
     * @param array<int, string> $availablePrograms
     */
    protected function matchStudyProgram(string $input, array $availablePrograms): string
    {
        $inputTrimmed = trim($input);
        if ($inputTrimmed === '') {
            return $availablePrograms[0];
        }

        // 1. Exact match
        foreach ($availablePrograms as $p) {
            if (strcasecmp($p, $inputTrimmed) === 0) {
                return $p;
            }
        }

        // 2. Substring match
        $inputLower = strtolower($inputTrimmed);
        foreach ($availablePrograms as $p) {
            $pLower = strtolower($p);
            if (str_contains($pLower, $inputLower) || str_contains($inputLower, $pLower)) {
                return $p;
            }
        }

        // 3. Fallback default
        return $availablePrograms[0];
    }

    /**
     * Map raw regular class string to supported enum key.
     */
    protected function matchRegularClass(string $input): string
    {
        $clean = strtolower(trim($input));

        if (str_contains($clean, 'ck') || str_contains($clean, 'kamis')) {
            return 'reg_ck';
        }

        if (str_contains($clean, 'cs') || str_contains($clean, 'sabtu')) {
            return 'reg_cs';
        }

        if (str_contains($clean, 'b') || str_contains($clean, 'malam')) {
            return 'reg_b';
        }

        return 'reg_a';
    }

    /**
     * Generate template CSV content with sample rows.
     */
    public function generateTemplateCsv(): string
    {
        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['nim', 'nama', 'jurusan', 'reguler', 'nomor_wa', 'semester', 'catatan']);

        fputcsv($output, ['211011400001', 'Budi Santoso', 'Teknik Informatika S1', 'Reguler A (Pagi)', '6281234567001', '5', 'Mahasiswa Reguler Pagi']);
        fputcsv($output, ['211011400002', 'Siti Aminah', 'Sistem Informasi S1', 'Reguler B (Malam)', '6281234567002', '3', 'Mahasiswa Reguler Malam']);
        fputcsv($output, ['211011400003', 'Ahmad Fauzi', 'Manajemen S1', 'Reguler CK (Kamis)', '6281234567003', '7', 'Mahasiswa Reguler Kamis']);
        fputcsv($output, ['211011400004', 'Rina Marlina', 'Akuntansi S1', 'Reguler CS (Sabtu)', '6281234567004', '1', 'Mahasiswa Reguler Sabtu']);

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content ?: '';
    }
}

