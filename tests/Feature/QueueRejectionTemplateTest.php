<?php

namespace Tests\Feature;

use App\Models\Queue\QueueRejectionTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QueueRejectionTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        Role::findOrCreate('Super Administrator', 'web');
        Role::findOrCreate('Queue Administrator', 'web');
    }

    public function test_can_retrieve_active_templates_ordered(): void
    {
        QueueRejectionTemplate::truncate();

        QueueRejectionTemplate::create([
            'title'      => 'Template B',
            'content'    => 'Isi B',
            'type'       => 'all',
            'sort_order' => 2,
            'is_active'  => true,
        ]);

        QueueRejectionTemplate::create([
            'title'      => 'Template A',
            'content'    => 'Isi A',
            'type'       => 'all',
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        QueueRejectionTemplate::create([
            'title'      => 'Template Inactive',
            'content'    => 'Isi Inactive',
            'type'       => 'all',
            'sort_order' => 0,
            'is_active'  => false,
        ]);

        $active = QueueRejectionTemplate::getActiveTemplates();

        $this->assertCount(2, $active);
        $this->assertEquals('Template A', $active[0]['title']);
        $this->assertEquals('Template B', $active[1]['title']);
    }

    public function test_admin_can_create_template(): void
    {
        $admin = User::factory()->create(['username' => 'admin_test1']);
        $admin->assignRole('Super Administrator');

        $response = $this->actingAs($admin)
            ->post(route('admin.queue.rejection-templates.store'), [
                'title'      => 'Template Baru',
                'content'    => 'Mohon maaf permohonan ditolak karena dokumen buram.',
                'type'       => 'all',
                'sort_order' => 10,
                'is_active'  => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('queue_rejection_templates', [
            'title' => 'Template Baru',
        ]);
    }

    public function test_admin_can_update_template(): void
    {
        $admin = User::factory()->create(['username' => 'admin_test2']);
        $admin->assignRole('Super Administrator');

        $template = QueueRejectionTemplate::create([
            'title'      => 'Template Lama',
            'content'    => 'Isi Lama',
            'type'       => 'all',
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.queue.rejection-templates.update', $template->id), [
                'title'      => 'Template Baru Diubah',
                'content'    => 'Isi baru yang sudah direvisi secara sopan.',
                'type'       => 'rejection',
                'sort_order' => 5,
                'is_active'  => false,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('queue_rejection_templates', [
            'id'        => $template->id,
            'title'     => 'Template Baru Diubah',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_template(): void
    {
        $admin = User::factory()->create(['username' => 'admin_test3']);
        $admin->assignRole('Super Administrator');

        $template = QueueRejectionTemplate::create([
            'title'      => 'Template Dihapus',
            'content'    => 'Isi',
            'type'       => 'all',
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.queue.rejection-templates.destroy', $template->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('queue_rejection_templates', [
            'id' => $template->id,
        ]);
    }
}
