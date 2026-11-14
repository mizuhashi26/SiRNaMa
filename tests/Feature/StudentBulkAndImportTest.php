<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StudentBulkAndImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_admin_forbidden_from_student_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.students.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_download_csv_template(): void
    {
        $admin = User::factory()->superadmin()->create();

        $response = $this->actingAs($admin)->get(route('admin.students.template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('nim,nama,jurusan,reguler,nomor_wa,semester,catatan', $response->getContent());
    }

    public function test_admin_can_import_students_from_csv(): void
    {
        $admin = User::factory()->superadmin()->create();

        $csvContent = "nim,nama,jurusan,reguler,nomor_wa,semester,catatan\n" .
                      "211011400101,Budi Santoso,Sistem Informasi S1,Reguler A (Pagi),628123456701,5,Reguler A\n" .
                      "211011400102,Dewi Sartika,Teknik Informatika S1,Reguler B (Malam),08123456702,3,Reguler B\n" .
                      "211011400103,Ahmad Fauzi,Manajemen S1,Reguler CK (Kamis),08123456703,7,Reguler CK\n" .
                      "211011400104,Rina Marlina,Akuntansi S1,Reguler CS (Sabtu),08123456704,1,Reguler CS\n";

        $file = UploadedFile::fake()->createWithContent('mahasiswa.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nim' => '211011400101',
            'name' => 'Budi Santoso',
            'study_program' => 'Sistem Informasi S1',
            'regular_class' => 'reg_a',
            'whatsapp_number' => '628123456701',
        ]);

        $this->assertDatabaseHas('students', [
            'nim' => '211011400102',
            'name' => 'Dewi Sartika',
            'study_program' => 'Teknik Informatika S1',
            'regular_class' => 'reg_b',
            'whatsapp_number' => '628123456702', // Normalized
        ]);

        $this->assertDatabaseHas('students', [
            'nim' => '211011400103',
            'regular_class' => 'reg_ck',
        ]);

        $this->assertDatabaseHas('students', [
            'nim' => '211011400104',
            'regular_class' => 'reg_cs',
        ]);
    }

    public function test_admin_can_filter_students_by_regular_class(): void
    {
        $admin = User::factory()->superadmin()->create();

        Student::create([
            'nim' => '211011400201',
            'name' => 'Mahasiswa Reg A',
            'study_program' => 'Teknik Informatika S1',
            'regular_class' => 'reg_a',
            'semester' => 1,
            'whatsapp_number' => '6281234567201',
            'is_active' => true,
        ]);

        Student::create([
            'nim' => '211011400202',
            'name' => 'Mahasiswa Reg CK',
            'study_program' => 'Teknik Informatika S1',
            'regular_class' => 'reg_ck',
            'semester' => 3,
            'whatsapp_number' => '6281234567202',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.students.index', ['regular_class' => 'reg_ck']));
        $response->assertOk();
        $response->assertSee('Mahasiswa Reg CK');
        $response->assertDontSee('Mahasiswa Reg A');
    }

    public function test_admin_can_bulk_delete_selected_students(): void
    {
        $admin = User::factory()->superadmin()->create();

        $s1 = Student::create([
            'nim' => '211011400011',
            'name' => 'Mahasiswa 1',
            'study_program' => 'Sistem Informasi S1',
            'semester' => 3,
            'whatsapp_number' => '628123456711',
            'is_active' => true,
        ]);

        $s2 = Student::create([
            'nim' => '211011400012',
            'name' => 'Mahasiswa 2',
            'study_program' => 'Teknik Informatika S1',
            'semester' => 5,
            'whatsapp_number' => '628123456712',
            'is_active' => true,
        ]);

        $s3 = Student::create([
            'nim' => '211011400013',
            'name' => 'Mahasiswa 3',
            'study_program' => 'Manajemen S1',
            'semester' => 1,
            'whatsapp_number' => '628123456713',
            'is_active' => true,
        ]);

        // Hapus s1 dan s2 saja
        $response = $this->actingAs($admin)->post(route('admin.students.bulk-delete'), [
            'selected_ids' => [$s1->id, $s2->id],
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('students', ['id' => $s1->id]);
        $this->assertDatabaseMissing('students', ['id' => $s2->id]);
        $this->assertDatabaseHas('students', ['id' => $s3->id]);
    }

    public function test_admin_can_delete_all_students(): void
    {
        $admin = User::factory()->superadmin()->create();

        Student::create([
            'nim' => '211011400021',
            'name' => 'Mahasiswa A',
            'study_program' => 'Sistem Informasi S1',
            'semester' => 1,
            'whatsapp_number' => '628123456721',
            'is_active' => true,
        ]);

        $this->assertTrue(Student::count() > 0);

        $response = $this->actingAs($admin)->delete(route('admin.students.delete-all'));

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');
        $this->assertEquals(0, Student::count());
    }

    public function test_bulk_create_page_requires_superadmin_and_students_index_shows_bulk_link(): void
    {
        $regularAdmin = \App\Models\User::factory()->create(['role' => 'admin']);
        $superAdmin = \App\Models\User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($regularAdmin)
            ->get(route('admin.students.bulk-create'))
            ->assertForbidden();

        $this->actingAs($regularAdmin)
            ->post(route('admin.students.bulk-store'), [
                'mode' => 'rows',
                'rows' => [],
            ])
            ->assertForbidden();

        $indexResponse = $this->actingAs($superAdmin)->get(route('admin.students.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee(route('admin.students.bulk-create'));
        $indexResponse->assertSee('+ Tambah Massal');

        $bulkResponse = $this->actingAs($superAdmin)->get(route('admin.students.bulk-create'));
        $bulkResponse->assertOk();
        $bulkResponse->assertSee('bulk_text');
        $bulkResponse->assertSee('Tambah Mahasiswa Massal');
    }

    public function test_superadmin_can_bulk_store_students_via_interactive_rows_and_respects_duplicate_mode(): void
    {
        $superAdmin = \App\Models\User::factory()->create(['role' => 'superadmin']);
        $prodi = Student::STUDY_PROGRAMS[0];
        $regularClass = array_key_first(Student::REGULAR_CLASSES);

        Student::create([
            'nim' => '241011400001',
            'name' => 'Mahasiswa Lama',
            'study_program' => $prodi,
            'regular_class' => $regularClass,
            'semester' => 2,
            'whatsapp_number' => '628111111111',
            'is_active' => true,
        ]);

        // Skip existing + ignore completely blank trailing row + create 2 new rows
        $skipResponse = $this->actingAs($superAdmin)->post(route('admin.students.bulk-store'), [
            'mode' => 'rows',
            'duplicate_mode' => 'skip',
            'rows' => [
                [
                    'nim' => '241011400001',
                    'name' => 'Nama Baru Diabaikan',
                    'study_program' => $prodi,
                    'regular_class' => $regularClass,
                    'semester' => 4,
                    'whatsapp_number' => '089999999999',
                    'is_active' => '1',
                    'notes' => 'Skip',
                ],
                [
                    'nim' => '241011400002',
                    'name' => 'Aulia Rahman',
                    'study_program' => $prodi,
                    'regular_class' => $regularClass,
                    'semester' => 3,
                    'whatsapp_number' => '081234567890',
                    'is_active' => '1',
                    'notes' => 'Aktif',
                ],
                [
                    'nim' => '241011400003',
                    'name' => 'Siti Aminah',
                    'study_program' => $prodi,
                    'regular_class' => $regularClass,
                    'semester' => 5,
                    'whatsapp_number' => '6282233445566',
                    'is_active' => '1',
                    'notes' => '',
                ],
                [
                    'nim' => '',
                    'name' => '',
                    'study_program' => $prodi,
                    'regular_class' => $regularClass,
                    'semester' => '',
                    'whatsapp_number' => '',
                    'notes' => '',
                ],
            ],
        ]);

        $skipResponse->assertRedirect();
        $skipResponse->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nim' => '241011400001',
            'name' => 'Mahasiswa Lama',
        ]);
        $this->assertDatabaseHas('students', [
            'nim' => '241011400002',
            'name' => 'Aulia Rahman',
            'whatsapp_number' => '6281234567890',
        ]);
        $this->assertDatabaseHas('students', [
            'nim' => '241011400003',
            'name' => 'Siti Aminah',
            'whatsapp_number' => '6282233445566',
        ]);

        // Update existing NIM when duplicate_mode = update
        $updateResponse = $this->actingAs($superAdmin)->post(route('admin.students.bulk-store'), [
            'mode' => 'rows',
            'duplicate_mode' => 'update',
            'rows' => [
                [
                    'nim' => '241011400001',
                    'name' => 'Mahasiswa Terupdate',
                    'study_program' => $prodi,
                    'regular_class' => $regularClass,
                    'semester' => 6,
                    'whatsapp_number' => '081987654321',
                    'is_active' => '1',
                    'notes' => 'Updated',
                ],
            ],
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');
        $this->assertDatabaseHas('students', [
            'nim' => '241011400001',
            'name' => 'Mahasiswa Terupdate',
            'whatsapp_number' => '6281987654321',
            'semester' => 6,
        ]);

        // Duplicate NIM within same batch + invalid phone should fail validation
        $invalidResponse = $this->actingAs($superAdmin)->post(route('admin.students.bulk-store'), [
            'mode' => 'rows',
            'duplicate_mode' => 'update',
            'rows' => [
                [
                    'nim' => '241011400099',
                    'name' => 'Duplikat Satu',
                    'study_program' => $prodi,
                    'regular_class' => $regularClass,
                    'semester' => 1,
                    'whatsapp_number' => '081234567891',
                ],
                [
                    'nim' => '241011400099',
                    'name' => 'Duplikat Dua',
                    'study_program' => $prodi,
                    'regular_class' => $regularClass,
                    'semester' => 1,
                    'whatsapp_number' => '123',
                ],
            ],
        ]);

        $invalidResponse->assertSessionHasErrors();
        $this->assertDatabaseMissing('students', ['nim' => '241011400099']);
    }

    public function test_superadmin_can_bulk_store_students_via_pasted_text_with_minimal_and_full_formats(): void
    {
        $superAdmin = \App\Models\User::factory()->create(['role' => 'superadmin']);
        $prodi = Student::STUDY_PROGRAMS[0];
        $regularClass = array_key_first(Student::REGULAR_CLASSES);

        Student::create([
            'nim' => '241011400010',
            'name' => 'Data Eksisting',
            'study_program' => $prodi,
            'regular_class' => $regularClass,
            'semester' => 2,
            'whatsapp_number' => '628111111110',
            'is_active' => true,
        ]);

        $pipeText = implode("\n", [
            'NIM | Nama | Prodi | Reguler | No WA | Semester | Catatan',
            '241011400011 | Budi Santoso | 081234567890',
            "241011400012 | Rina Kusuma | {$prodi} | {$regularClass} | 6281355556666 | 4 | Mahasiswa pindahan",
            '241011400010 | Nama Tidak Berubah | 081999999999',
        ]);

        $skipResponse = $this->actingAs($superAdmin)->post(route('admin.students.bulk-store'), [
            'mode' => 'text',
            'duplicate_mode' => 'skip',
            'bulk_text' => $pipeText,
        ]);

        $skipResponse->assertRedirect();
        $skipResponse->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nim' => '241011400011',
            'name' => 'Budi Santoso',
            'whatsapp_number' => '6281234567890',
        ]);
        $this->assertDatabaseHas('students', [
            'nim' => '241011400012',
            'name' => 'Rina Kusuma',
            'whatsapp_number' => '6281355556666',
            'semester' => 4,
            'notes' => 'Mahasiswa pindahan',
        ]);
        $this->assertDatabaseHas('students', [
            'nim' => '241011400010',
            'name' => 'Data Eksisting',
        ]);

        // Comma-delimited update mode
        $updateResponse = $this->actingAs($superAdmin)->post(route('admin.students.bulk-store'), [
            'mode' => 'text',
            'duplicate_mode' => 'update',
            'bulk_text' => '241011400010, Data Diperbarui, 081777888999',
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');
        $this->assertDatabaseHas('students', [
            'nim' => '241011400010',
            'name' => 'Data Diperbarui',
            'whatsapp_number' => '6281777888999',
        ]);

        // Invalid pasted text should fail with bulk_text error
        $invalidTextResponse = $this->actingAs($superAdmin)->post(route('admin.students.bulk-store'), [
            'mode' => 'text',
            'duplicate_mode' => 'update',
            'bulk_text' => 'baris sembarang tanpa nim valid',
        ]);

        $invalidTextResponse->assertSessionHasErrors('bulk_text');
    }

    public function test_admin_sidebar_logout_button_has_confirmation_attribute(): void
    {
        $superAdmin = \App\Models\User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'));
        $response->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString('id="admin-sidebar"', $html);

        $sidebarStart = strpos($html, 'id="admin-sidebar"');
        $sidebarEnd = strpos($html, '</aside>', $sidebarStart);
        $this->assertNotFalse($sidebarStart);
        $this->assertNotFalse($sidebarEnd);

        $sidebarHtml = substr($html, $sidebarStart, $sidebarEnd - $sidebarStart);
        $this->assertStringContainsString(
            'data-confirm="Apakah Anda yakin ingin keluar dari sesi admin?"',
            $sidebarHtml
        );
    }
}
