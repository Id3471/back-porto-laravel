<?php

namespace Tests\Feature;

use App\Mail\PortfolioContactMessage;
use App\Models\PortfolioContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_sends_a_message_to_the_configured_inbox(): void
    {
        Mail::fake();
        Config::set('mail.contact_to', 'owner@example.com');

        $this->postJson('/contact', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.net',
            'subject' => 'Projet intéressant',
            'message' => '<script>alert("test")</script> Bonjour, je souhaite discuter de ce projet.',
            'website' => '',
        ])->assertOk()
            ->assertJsonPath('message', 'Votre message a bien été envoyé.');

        Mail::assertSent(PortfolioContactMessage::class, function (PortfolioContactMessage $mail): bool {
            return $mail->hasTo('owner@example.com')
                && $mail->hasReplyTo('ada@example.net', 'Ada Lovelace')
                && $mail->envelope()->subject === 'Projet intéressant'
                && str_contains($mail->render(), '&lt;script&gt;')
                && ! str_contains($mail->render(), '<script>');
        });
    }

    public function test_contact_form_is_rate_limited(): void
    {
        Mail::fake();
        Config::set('mail.contact_to', 'owner@example.com');

        $message = [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.net',
            'message' => 'Bonjour, je souhaite discuter de ce projet.',
            'website' => '',
        ];

        foreach (range(1, 3) as $attempt) {
            $this->postJson('/contact', $message)->assertOk();
        }

        $this->postJson('/contact', $message)->assertTooManyRequests();
        Mail::assertSentCount(3);
    }

    public function test_contact_form_rejects_invalid_input_and_honeypot_submissions(): void
    {
        Mail::fake();

        $this->postJson('/contact', [
            'name' => 'Visiteur',
            'email' => 'not-an-email',
            'message' => 'Texte trop court',
            'website' => 'https://spam.example',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'website']);

        Mail::assertNothingSent();
    }

    public function test_contact_form_reports_missing_mail_configuration(): void
    {
        Mail::fake();
        Config::set('mail.contact_to', null);

        $this->postJson('/contact', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.net',
            'message' => 'Bonjour, je souhaite discuter de ce projet.',
            'website' => '',
        ])->assertServiceUnavailable()
            ->assertJsonPath(
                'message',
                'La réception des messages n’est pas encore configurée. Veuillez réessayer plus tard.',
            );

        Mail::assertNothingSent();
    }

    public function test_public_cv_route_downloads_only_the_cv_published_by_the_admin(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('cv/cv-public-123.pdf', 'example pdf');
        PortfolioContent::query()->create([
            'key' => 'portfolio',
            'data' => ['profile' => ['cvUrl' => '/storage/cv/cv-public-123.pdf']],
        ]);

        $this->get('/cv')
            ->assertOk()
            ->assertDownload('CV-portfolio.pdf');
    }

    public function test_public_cv_route_returns_not_found_when_no_cv_is_published(): void
    {
        $this->get('/cv')->assertNotFound();
    }
}
