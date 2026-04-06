<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_admin_can_access_main_panel_pages(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $this->actingAs($admin);

        $this->get('/admin')->assertOk();
        $this->get('/admin/buku-kas')->assertOk();
        $this->get('/admin/setting')->assertOk();
        $this->get('/admin/categories')->assertOk();
    }

    public function test_regular_user_cannot_access_category_management(): void
    {
        $user = User::where('username', 'investor')->firstOrFail();

        $this->actingAs($user);

        $this->get('/admin/categories')->assertForbidden();
    }
}
