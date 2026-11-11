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
}
