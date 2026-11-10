<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\StudentImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

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
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.students.form', [
            'student' => new Student(['is_active' => true, 'semester' => 1, 'study_program' => Student::STUDY_PROGRAMS[0]]),
            'studyPrograms' => Student::STUDY_PROGRAMS,
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'size:12', 'regex:/^[0-9]{12}$/', 'unique:students,nim'],
            'name' => ['required', 'string', 'max:150'],
            'study_program' => ['required', 'string', Rule::in(Student::STUDY_PROGRAMS)],
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
            'name.required' => 'Nama lengkap mahasiswa wajib diisi.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka.',
        ]);

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
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'size:12', 'regex:/^[0-9]{12}$/', Rule::unique('students', 'nim')->ignore($student->id)],
            'name' => ['required', 'string', 'max:150'],
            'study_program' => ['required', 'string', Rule::in(Student::STUDY_PROGRAMS)],
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
        ]);

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

