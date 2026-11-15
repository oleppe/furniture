<?php

namespace Tests\Feature;

use App\Models\AiModel;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Main Administrator',
            'email' => 'ap.sy.uk@gmail.com',
            'password' => Hash::make('Ff123123@'),
            'role' => 'admin',
        ]);
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('admin.login'));
    }

    public function test_root_url_shows_public_landing_page(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('إدراك Lab');
    }

    public function test_admin_can_login_with_specified_credentials(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'ap.sy.uk@gmail.com',
            'password' => 'Ff123123@',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->adminUser);
    }

    public function test_non_admin_user_cannot_access_admin_portal(): void
    {
        $regularUser = User::create([
            'name' => 'Standard Member',
            'email' => 'member@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($regularUser)->get(route('admin.dashboard'));
        $response->assertForbidden();
    }

    public function test_admin_can_view_models_and_filter(): void
    {
        $provider = Provider::create([
            'name' => 'OpenAI',
            'slug' => 'openai',
        ]);

        $model = AiModel::create([
            'provider_id' => $provider->id,
            'name' => 'GPT-4o',
            'slug' => 'openai/gpt-4o',
            'is_open_source' => false,
            'is_active' => true,
            'is_featured' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.models.index'));
        $response->assertOk();
        $response->assertSee('GPT-4o');
        $response->assertSee('OpenAI');
    }

    public function test_admin_can_view_model_profile_show_page(): void
    {
        $provider = Provider::create([
            'name' => 'Mistral AI',
            'slug' => 'mistral',
        ]);

        $model = AiModel::create([
            'provider_id' => $provider->id,
            'name' => 'Mistral Large 2',
            'slug' => 'mistral/mistral-large',
            'is_open_source' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.models.show', $model));
        $response->assertOk();
        $response->assertSee('Mistral Large 2');
        $response->assertSee('Overview & Specifications', false);
        $response->assertSee('Pricing & Caching Matrix', false);
        $response->assertSee('Benchmarks & Arabic Metrics', false);
    }

    public function test_admin_can_fetch_comprehensive_model_offerings_and_properties(): void
    {
        $provider = Provider::create([
            'name' => 'Meta',
            'slug' => 'meta',
        ]);

        $model = AiModel::create([
            'provider_id' => $provider->id,
            'name' => 'Llama 3.3 70B',
            'slug' => 'meta-llama/llama-3.3-70b-instruct',
            'canonical_slug' => 'meta-llama/llama-3.3-70b-instruct-20260901',
            'hugging_face_id' => 'meta-llama/Llama-3.3-70B-Instruct',
            'openrouter_id' => 'meta-llama/llama-3.3-70b-instruct',
            'architecture' => 'text->text',
            'is_open_source' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.models.offerings', $model));
        $response->assertOk();
        $response->assertJsonStructure([
            'model' => [
                'id',
                'name',
                'slug',
                'canonical_slug',
                'hugging_face_id',
                'openrouter_id',
                'provider',
                'architecture',
                'show_url',
            ],
            'spec',
            'benchmark_general',
            'benchmark_arabic',
            'speed',
            'offerings',
            'raw_database_record',
        ]);
        $this->assertEquals('Llama 3.3 70B', $response->json('model.name'));
        $this->assertEquals('meta-llama/Llama-3.3-70B-Instruct', $response->json('model.hugging_face_id'));
    }

    public function test_admin_can_toggle_model_active_and_featured(): void
    {
        $provider = Provider::create([
            'name' => 'Anthropic',
            'slug' => 'anthropic',
        ]);

        $model = AiModel::create([
            'provider_id' => $provider->id,
            'name' => 'Claude 3.5 Sonnet',
            'slug' => 'anthropic/claude-3-5-sonnet',
            'is_active' => true,
            'is_featured' => false,
        ]);

        $this->actingAs($this->adminUser)->post(route('admin.models.toggle-active', $model));
        $this->assertFalse($model->fresh()->is_active);

        $this->actingAs($this->adminUser)->post(route('admin.models.toggle-featured', $model));
        $this->assertTrue($model->fresh()->is_featured);
    }

    public function test_admin_can_create_new_user(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.users.store'), [
            'name' => 'New Editor',
            'email' => 'editor@aiplatform.com',
            'role' => 'editor',
            'password' => 'SecurePass123!',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'editor@aiplatform.com',
            'role' => 'editor',
        ]);
    }

    public function test_admin_can_update_existing_user(): void
    {
        $user = User::create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'role' => 'user',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.users.update', $user), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'editor',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'editor',
        ]);
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $response = $this->actingAs($this->adminUser)->put(route('admin.users.update', $this->adminUser), [
            'name' => 'Admin Renamed',
            'email' => $this->adminUser->email,
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertEquals('admin', $this->adminUser->fresh()->role);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->adminUser)->delete(route('admin.users.destroy', $this->adminUser));
        $response->assertSessionHasErrors(['error']);
        $this->assertDatabaseHas('users', ['id' => $this->adminUser->id]);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $userToDelete = User::create([
            'name' => 'To Delete',
            'email' => 'delete_me@example.com',
            'role' => 'user',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.users.destroy', $userToDelete));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    public function test_admin_portal_and_login_include_theme_toggle(): void
    {
        // Admin layout has theme switcher and anti-FOUC script
        $dashboardResponse = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('toggleTheme()', false);
        $dashboardResponse->assertSee('admin_theme', false);
        $dashboardResponse->assertSee('Toggle theme mode', false);

        // Login page has theme switcher and anti-FOUC script
        auth()->logout();
        $loginResponse = $this->get(route('admin.login'));
        $loginResponse->assertOk();
        $loginResponse->assertSee('toggleTheme()', false);
        $loginResponse->assertSee('admin_theme', false);
    }

    public function test_admin_area_is_excluded_from_search_indexes(): void
    {
        $dashboardResponse = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('<meta name="robots" content="noindex, nofollow, noarchive">', false);
    }
}
