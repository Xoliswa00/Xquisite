<?php

namespace Tests\Feature\PublicLaunch;

use App\Models\PublicLaunch;
use App\Models\PublicQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLaunchTest extends TestCase
{
    use RefreshDatabase;

    public function test_founding_20_seed_row_exists_after_migration(): void
    {
        $this->assertDatabaseHas('public_launches', ['key' => 'founding-20', 'is_active' => 1]);
    }

    public function test_active_launch_page_renders(): void
    {
        $response = $this->get(route('founding-20.show'));

        $response->assertOk();
        $response->assertSee('Founding 20');
    }

    public function test_inactive_launch_page_404s(): void
    {
        PublicLaunch::where('key', 'founding-20')->update(['is_active' => false]);

        $response = $this->get(route('founding-20.show'));

        $response->assertNotFound();
    }

    public function test_unknown_launch_key_404s(): void
    {
        $response = $this->get(route('public-launch.show', 'does-not-exist'));

        $response->assertNotFound();
    }

    public function test_visitor_can_ask_a_question(): void
    {
        $response = $this->post(route('founding-20.questions.store'), [
            'asker_name'  => 'Jane',
            'asker_email' => 'jane@example.com',
            'question'    => 'When does this launch?',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('public_questions', [
            'question'    => 'When does this launch?',
            'asker_name'  => 'Jane',
            'is_published' => 0,
        ]);
    }

    public function test_question_requires_text(): void
    {
        $response = $this->post(route('founding-20.questions.store'), ['question' => '']);

        $response->assertSessionHasErrors('question');
        $this->assertSame(0, PublicQuestion::count());
    }

    public function test_asking_a_question_fails_when_qa_disabled(): void
    {
        PublicLaunch::where('key', 'founding-20')->update(['qa_enabled' => false]);

        $response = $this->post(route('founding-20.questions.store'), ['question' => 'Anything?']);

        $response->assertNotFound();
    }

    public function test_only_published_answered_questions_show_publicly(): void
    {
        $launch = PublicLaunch::where('key', 'founding-20')->first();

        $launch->questions()->create(['question' => 'Unanswered', 'is_published' => false]);
        $launch->questions()->create(['question' => 'Answered but hidden', 'answer' => 'Yes.', 'answered_at' => now(), 'is_published' => false]);
        $launch->questions()->create(['question' => 'Answered and public', 'answer' => 'Definitely.', 'answered_at' => now(), 'is_published' => true]);

        $response = $this->get(route('founding-20.show'));

        $response->assertSee('Answered and public');
        $response->assertDontSee('Unanswered');
        $response->assertDontSee('Answered but hidden');
    }

    public function test_qa_section_hidden_when_disabled(): void
    {
        PublicLaunch::where('key', 'founding-20')->update(['qa_enabled' => false]);

        $response = $this->get(route('founding-20.show'));

        $response->assertOk();
        $response->assertDontSee('Ask your question');
    }
}
