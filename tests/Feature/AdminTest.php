<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_admin_routes_reject_guests(): void
    {
        $this->getJson('/admin-api/session')->assertUnauthorized();
        $this->putJson('/admin-api/content', ['data' => []])->assertUnauthorized();
    }

    public function test_authenticated_non_admins_cannot_open_private_routes(): void
    {
        $user = User::factory()->create([
            'username' => 'regular-user',
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->getJson('/admin-api/session')
            ->assertForbidden();
    }

    public function test_admin_can_log_in_and_manage_account_credentials(): void
    {
        User::factory()->create([
            'name' => 'Portfolio Owner',
            'username' => 'portfolio-admin',
            'password' => 'StrongPassword1!',
            'is_admin' => true,
        ]);

        $this->postJson('/admin-api/login', [
            'username' => 'PORTFOLIO-ADMIN',
            'password' => 'StrongPassword1!',
        ])->assertOk()
            ->assertJsonPath('user.username', 'portfolio-admin');

        $this->putJson('/admin-api/account', [
            'username' => 'new-admin-name',
            'current_password' => 'StrongPassword1!',
            'password' => 'AnotherStrong2@',
            'password_confirmation' => 'AnotherStrong2@',
        ])->assertOk()
            ->assertJsonPath('user.username', 'new-admin-name');

        $this->getJson('/admin-api/session')->assertOk();

        $this->assertDatabaseHas('users', [
            'username' => 'new-admin-name',
            'is_admin' => true,
        ]);
    }

    public function test_public_content_endpoint_returns_no_unpublished_content(): void
    {
        $this->getJson('/portfolio-content')
            ->assertOk()
            ->assertExactJson(['data' => null]);
    }

    public function test_admin_can_publish_content_for_the_public_site(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $content = [
            'profile' => [
                'firstName' => 'Awa',
                'lastName' => 'Diallo',
                'fullName' => 'Awa Diallo',
                'role' => 'Développeuse',
                'roles' => ['Développeuse'],
                'location' => 'Abidjan',
                'email' => 'awa@example.com',
                'phones' => ['+225 00 00 00 00'],
                'summary' => 'Présentation du portfolio.',
                'socials' => [],
                'languages' => [],
                'interests' => [],
            ],
            'projects' => [],
            'projectCategories' => [],
            'skillGroups' => [],
            'softSkills' => [],
            'experiences' => [],
            'education' => [],
            'certifications' => [],
        ];

        $this->actingAs($admin)
            ->putJson('/admin-api/content', ['data' => $content])
            ->assertOk()
            ->assertJsonPath('data.profile.fullName', 'Awa Diallo');

        $this->getJson('/portfolio-content')
            ->assertOk()
            ->assertJsonPath('data.profile.email', 'awa@example.com');
    }

    public function test_admin_can_replace_the_public_cv_with_a_pdf(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->post('/admin-api/cv', [
                'cv' => UploadedFile::fake()->create('cv.pdf', 20, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $cvUrl = $response->json('cvUrl');
        $this->assertStringContainsString('/storage/cv/', $cvUrl);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', parse_url($cvUrl, PHP_URL_PATH)));
        $this->getJson('/portfolio-content')
            ->assertOk()
            ->assertJsonPath('data.profile.cvUrl', $cvUrl);
    }
}
