<?php

namespace Tests\Feature\PublicLaunch;

use App\Models\PublicLaunch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminPublicLaunchTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Permission::findOrCreate('manage-tenants', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('manage-tenants');

        return $user;
    }

    public function test_non_admin_cannot_access_index(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('admin.public-launches.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_update_countdown_and_toggles(): void
    {
        $launch = PublicLaunch::where('key', 'founding-20')->first();

        $response = $this->actingAs($this->admin())->patch(route('admin.public-launches.update', $launch), [
            'title'      => 'Founding 20',
            'tagline'    => 'Updated tagline',
            'benefits'   => "Benefit one\nBenefit two",
            'launch_at'  => '2026-12-01T09:00',
            'is_active'  => '1',
            'qa_enabled' => '0',
        ]);

        $response->assertRedirect(route('admin.public-launches.index'));

        $launch->refresh();
        $this->assertSame('Updated tagline', $launch->tagline);
        $this->assertSame(['Benefit one', 'Benefit two'], $launch->benefits);
        $this->assertFalse($launch->qa_enabled);
        $this->assertTrue($launch->is_active);
    }

    public function test_admin_can_answer_and_publish_a_question(): void
    {
        $launch = PublicLaunch::where('key', 'founding-20')->first();
        $question = $launch->questions()->create(['question' => 'How much does it cost?']);

        $response = $this->actingAs($this->admin())->patch(
            route('admin.public-launches.questions.answer', [$launch, $question]),
            ['answer' => 'R200/month after the free period.', 'is_published' => '1']
        );

        $response->assertRedirect();
        $question->refresh();
        $this->assertSame('R200/month after the free period.', $question->answer);
        $this->assertTrue($question->is_published);
        $this->assertNotNull($question->answered_at);
    }

    public function test_admin_can_remove_a_question(): void
    {
        $launch = PublicLaunch::where('key', 'founding-20')->first();
        $question = $launch->questions()->create(['question' => 'Spam question']);

        $response = $this->actingAs($this->admin())->delete(
            route('admin.public-launches.questions.destroy', [$launch, $question])
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('public_questions', ['id' => $question->id]);
    }

    public function test_question_from_a_different_launch_cannot_be_answered_via_this_launch(): void
    {
        $launch      = PublicLaunch::where('key', 'founding-20')->first();
        $otherLaunch = PublicLaunch::create(['key' => 'other-module', 'title' => 'Other Module']);
        $question    = $otherLaunch->questions()->create(['question' => 'Belongs elsewhere']);

        $response = $this->actingAs($this->admin())->patch(
            route('admin.public-launches.questions.answer', [$launch, $question]),
            ['answer' => 'Should not apply']
        );

        $response->assertNotFound();
    }
}
