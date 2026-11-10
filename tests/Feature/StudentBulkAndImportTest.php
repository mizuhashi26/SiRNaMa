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

    public function test_admin_can_download_csv_template(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.students.template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('nim,nama,jurusan,nomor_wa,semester,catatan', $response->getContent());
    }

    public function test_admin_can_import_students_from_csv(): void
    {
        $admin = User::factory()->create();

        $csvContent = "nim,nama,jurusan,nomor_wa,semester,catatan\n" .
                      "211011400101,Budi Santoso,Sistem Informasi S1,628123456701,5,Reguler A\n" .
                      "211011400102,Dewi Sartika,Teknik Informatika S1,08123456702,3,Reguler B\n";

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
            'whatsapp_number' => '628123456701',
        ]);

        $this->assertDatabaseHas('students', [
            'nim' => '211011400102',
            'name' => 'Dewi Sartika',
            'study_program' => 'Teknik Informatika S1',
            'whatsapp_number' => '628123456702', // Normalized
        ]);
    }

    public function test_admin_can_bulk_delete_selected_students(): void
    {
        $admin = User::factory()->create();

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
        $admin = User::factory()->create();

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
