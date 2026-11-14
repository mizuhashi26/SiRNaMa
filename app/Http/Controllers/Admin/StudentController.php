<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\StudentImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $regularClass = (string) $request->query('regular_class', '');

        $students = Student::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('nim', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('study_program', 'like', "%{$search}%")
                        ->orWhere('whatsapp_number', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($regularClass !== '', fn ($q) => $q->where('regular_class', $regularClass))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'search' => $search,
            'status' => $status,
            'regularClass' => $regularClass,
            'regularClasses' => Student::REGULAR_CLASSES,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.students.form', [
            'student' => new Student([
                'name' => (string) $request->query('name', ''),
                'whatsapp_number' => (string) $request->query('whatsapp_number', ''),
                'is_active' => true,
                'semester' => 1,
                'study_program' => Student::STUDY_PROGRAMS[0],
                'regular_class' => 'reg_a',
            ]),
            'studyPrograms' => Student::STUDY_PROGRAMS,
            'regularClasses' => Student::REGULAR_CLASSES,
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'size:12', 'regex:/^[0-9]{12}$/', 'unique:students,nim'],
            'name' => ['required', 'string', 'max:150'],
            'study_program' => ['required', 'string', Rule::in(Student::STUDY_PROGRAMS)],
            'regular_class' => ['nullable', 'string', Rule::in(array_keys(Student::REGULAR_CLASSES))],
            'semester' => ['required', 'integer', 'min:1', 'max:14'],
            'whatsapp_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'nim.required' => 'NIM mahasiswa wajib diisi.',
            'nim.size' => 'NIM mahasiswa harus terdiri dari tepat 12 digit angka.',
            'nim.regex' => 'NIM mahasiswa hanya boleh berisi angka 12 digit.',
            'nim.unique' => 'NIM ini sudah terdaftar.',
            'study_program.required' => 'Program studi wajib dipilih.',
            'study_program.in' => 'Program studi yang dipilih tidak valid.',
            'regular_class.in' => 'Pilihan kelas reguler tidak valid.',
            'name.required' => 'Nama lengkap mahasiswa wajib diisi.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka.',
        ]);

        $validated['regular_class'] = $validated['regular_class'] ?? 'reg_a';
        $validated['regular_class'] = $validated['regular_class'] ?? $student->regular_class ?? 'reg_a';
        $validated['whatsapp_number'] = Student::normalizePhone($validated['whatsapp_number']);
        $validated['is_active'] = $request->boolean('is_active');

        Student::create($validated);

        return redirect()->route('admin.students.index')
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        return view('admin.students.form', [
            'student' => $student,
            'studyPrograms' => Student::STUDY_PROGRAMS,
            'regularClasses' => Student::REGULAR_CLASSES,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'size:12', 'regex:/^[0-9]{12}$/', Rule::unique('students', 'nim')->ignore($student->id)],
            'name' => ['required', 'string', 'max:150'],
            'study_program' => ['required', 'string', Rule::in(Student::STUDY_PROGRAMS)],
            'regular_class' => ['nullable', 'string', Rule::in(array_keys(Student::REGULAR_CLASSES))],
            'semester' => ['required', 'integer', 'min:1', 'max:14'],
            'whatsapp_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'nim.required' => 'NIM mahasiswa wajib diisi.',
            'nim.size' => 'NIM mahasiswa harus terdiri dari tepat 12 digit angka.',
            'nim.regex' => 'NIM mahasiswa hanya boleh berisi angka 12 digit.',
            'study_program.required' => 'Program studi wajib dipilih.',
            'study_program.in' => 'Program studi yang dipilih tidak valid.',
            'regular_class.required' => 'Pilihan kelas reguler wajib diisi.',
            'regular_class.in' => 'Pilihan kelas reguler tidak valid.',
        ]);

        $validated['regular_class'] = $validated['regular_class'] ?? $student->regular_class ?? 'reg_a';
        $validated['whatsapp_number'] = Student::normalizePhone($validated['whatsapp_number']);
        $validated['is_active'] = $request->boolean('is_active');

        $student->update($validated);

        return redirect()->route('admin.students.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Data mahasiswa berhasil dihapus.');
    }

    /**
     * Import students from Excel (.xlsx) or CSV file.
     */
    public function import(Request $request, StudentImportService $importService): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'], // 5MB max
        ], [
            'file.required' => 'Pilih file Excel (.xlsx) atau CSV (.csv) untuk diunggah.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (! in_array($ext, ['csv', 'txt', 'xlsx', 'xls'])) {
            return redirect()->route('admin.students.index')
                ->withErrors(['file' => 'Format file tidak didukung. Harap unggah file .xlsx atau .csv.']);
        }

        try {
            $result = $importService->import($file);

            $msg = "Proses impor selesai: {$result['imported']} data baru ditambahkan, {$result['updated']} data diperbarui.";
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} baris dilewati karena format tidak sesuai).";
            }

            return redirect()->route('admin.students.index')
                ->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->route('admin.students.index')
                ->withErrors(['file' => 'Gagal memproses file: ' . $e->getMessage()]);
        }
    }

    /**
     * Download template CSV for batch student import.
     */
    public function downloadTemplate(StudentImportService $importService): Response
    {
        $csvContent = $importService->generateTemplateCsv();

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_data_mahasiswa_unpam.csv"',
        ]);
    }

    /**
     * Bulk delete selected students.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('selected_ids', []);

        if (empty($ids) || ! is_array($ids)) {
            return redirect()->route('admin.students.index')
                ->with('warning', 'Tidak ada mahasiswa yang dipilih untuk dihapus.');
        }

        $count = Student::whereIn('id', $ids)->delete();

        return redirect()->route('admin.students.index')
            ->with('success', "{$count} data mahasiswa terpilih berhasil dihapus.");
    }

    /**
     * Show bulk student creation page without CSV/Excel file upload.
     */
    public function bulkCreate(): View
    {
        return view('admin.students.bulk', [
            'studyPrograms' => Student::STUDY_PROGRAMS,
            'regularClasses' => Student::REGULAR_CLASSES,
        ]);
    }

    /**
     * Store multiple students from interactive multi-row form or pasted plain text.
     */
    public function bulkStore(Request $request, StudentImportService $importService): RedirectResponse
    {
        $request->validate([
            'mode' => ['required', 'string', Rule::in(['rows', 'text'])],
            'duplicate_mode' => ['nullable', 'string', Rule::in(['update', 'skip'])],
        ]);

        $mode = (string) $request->input('mode');
        $overwriteExisting = $request->input('duplicate_mode', 'update') !== 'skip';

        if ($mode === 'text') {
            $request->validate([
                'bulk_text' => ['required', 'string', 'max:100000'],
            ], [
                'bulk_text.required' => 'Daftar teks mahasiswa wajib diisi sebelum menyimpan.',
                'bulk_text.max' => 'Teks daftar mahasiswa melebihi batas maksimum karakter.',
            ]);

            $result = DB::transaction(fn () => $importService->importFromText(
                (string) $request->input('bulk_text'),
                $overwriteExisting
            ));

            if ($result['imported'] === 0 && $result['updated'] === 0) {
                $errorMessages = array_slice($result['errors'], 0, 8);
                if (empty($errorMessages)) {
                    $errorMessages = ['Tidak ada data mahasiswa valid yang dapat disimpan.'];
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['bulk_text' => $errorMessages]);
            }

            return $this->redirectBulkResult($result, 'Penambahan massal selesai');
        }

        $rawRows = $request->input('rows', []);
        if (! is_array($rawRows)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['rows' => 'Format input baris mahasiswa tidak valid.']);
        }

        $nonEmptyRows = [];
        foreach ($rawRows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $nim = trim((string) ($row['nim'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $phone = trim((string) ($row['whatsapp_number'] ?? ''));
            $notes = trim((string) ($row['notes'] ?? ''));

            if ($nim === '' && $name === '' && $phone === '' && $notes === '') {
                continue;
            }

            $nonEmptyRows[$index] = [
                '_line' => (string) ((int) $index + 1),
                'nim' => $nim,
                'name' => $name,
                'study_program' => (string) ($row['study_program'] ?? Student::STUDY_PROGRAMS[0]),
                'regular_class' => (string) ($row['regular_class'] ?? 'reg_a'),
                'semester' => (int) ($row['semester'] ?? 1),
                'whatsapp_number' => $phone,
                'is_active' => ! empty($row['is_active']),
                'notes' => $notes,
            ];
        }

        if (empty($nonEmptyRows)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['rows' => 'Isi minimal satu data mahasiswa (NIM, Nama, dan Nomor WhatsApp) sebelum menyimpan.']);
        }

        $validator = Validator::make(['rows' => $nonEmptyRows], [
            'rows.*.nim' => ['required', 'string', 'size:12', 'regex:/^[0-9]{12}$/'],
            'rows.*.name' => ['required', 'string', 'max:150'],
            'rows.*.study_program' => ['required', 'string', Rule::in(Student::STUDY_PROGRAMS)],
            'rows.*.regular_class' => ['required', 'string', Rule::in(array_keys(Student::REGULAR_CLASSES))],
            'rows.*.semester' => ['required', 'integer', 'min:1', 'max:14'],
            'rows.*.whatsapp_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
            'rows.*.notes' => ['nullable', 'string', 'max:500'],
        ], [
            'rows.*.nim.required' => 'NIM wajib diisi.',
            'rows.*.nim.size' => 'NIM harus tepat 12 digit angka.',
            'rows.*.nim.regex' => 'NIM hanya boleh berisi 12 digit angka.',
            'rows.*.name.required' => 'Nama lengkap mahasiswa wajib diisi.',
            'rows.*.study_program.in' => 'Program studi tidak valid.',
            'rows.*.regular_class.in' => 'Kelas reguler tidak valid.',
            'rows.*.semester.min' => 'Semester minimal 1.',
            'rows.*.semester.max' => 'Semester maksimal 14.',
            'rows.*.whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'rows.*.whatsapp_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka.',
        ]);

        $validator->after(function ($validator) use ($nonEmptyRows) {
            $seenNims = [];
            foreach ($nonEmptyRows as $index => $row) {
                $baris = (int) $index + 1;
                $nim = $row['nim'];
                if ($nim !== '') {
                    if (isset($seenNims[$nim])) {
                        $validator->errors()->add("rows.{$index}.nim", "Baris {$baris}: NIM {$nim} duplikat di dalam form ini.");
                    }
                    $seenNims[$nim] = true;
                }

                $normalizedWa = Student::normalizePhone($row['whatsapp_number']);
                if (strlen($normalizedWa) < 10) {
                    $validator->errors()->add("rows.{$index}.whatsapp_number", "Baris {$baris}: Nomor WhatsApp minimal 9-10 digit yang valid.");
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        $result = DB::transaction(fn () => $importService->importStructuredRows(
            array_values($nonEmptyRows),
            $overwriteExisting,
            true
        ));

        if ($result['imported'] === 0 && $result['updated'] === 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['rows' => array_slice($result['errors'], 0, 8)]);
        }

        return $this->redirectBulkResult($result, 'Tambah mahasiswa massal selesai');
    }

    /**
     * Redirect to student index with consistent bulk summary messages.
     *
     * @param array{total: int, imported: int, updated: int, skipped: int, errors: array<int, string>} $result
     */
    protected function redirectBulkResult(array $result, string $prefix): RedirectResponse
    {
        $parts = [];
        if ($result['imported'] > 0) {
            $parts[] = "{$result['imported']} mahasiswa baru ditambahkan";
        }
        if ($result['updated'] > 0) {
            $parts[] = "{$result['updated']} data mahasiswa diperbarui";
        }

        $msg = $prefix . ': ' . (empty($parts) ? 'tidak ada perubahan.' : implode(', ', $parts) . '.');
        $redirect = redirect()->route('admin.students.index')->with('success', $msg);

        if ($result['skipped'] > 0) {
            $sampleErrors = implode(' | ', array_slice($result['errors'], 0, 5));
            $redirect->with('warning', "{$result['skipped']} baris dilewati ({$sampleErrors}).");
        }

        return $redirect;
    }

    /**
     * Delete all students from database.
     */
    public function deleteAll(): RedirectResponse
    {
        $count = Student::count();
        Student::query()->delete();

        return redirect()->route('admin.students.index')
            ->with('success', "Seluruh data mahasiswa ({$count} data) berhasil dikosongkan.");
    }
}

