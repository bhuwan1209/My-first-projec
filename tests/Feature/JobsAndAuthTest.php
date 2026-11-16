<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Employer;
use App\Models\Job;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobsAndAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_contact_pages_return_200(): void
    {
        $this->get('/')->assertStatus(200)->assertSee('Home Page');
        $this->get('/contact')->assertStatus(200)->assertSee('Contact Page');
    }

    public function test_jobs_index_paginates_20_per_page_with_employer(): void
    {
        Job::factory(25)->create();

        $response = $this->get('/jobs');
        $response->assertStatus(200)->assertSee('Jobs Listing');
        $this->assertCount(20, $response->viewData('jobs'));
    }

    public function test_job_crud_flow(): void
    {
        $employer = Employer::factory()->create();

        $this->post('/jobs', [
            'title' => 'Laravel Developer',
            'salary' => '$55,000',
        ])->assertRedirect('/jobs');

        $job = Job::first();
        $this->assertNotNull($job);
        $this->assertSame($employer->id, $job->employer_id);

        $this->get("/jobs/{$job->id}")->assertStatus(200)->assertSee('Laravel Developer');

        $this->patch("/jobs/{$job->id}", [
            'title' => 'Senior Laravel Developer',
            'salary' => '$75,000',
        ])->assertRedirect("/jobs/{$job->id}");

        $this->assertDatabaseHas('job_listings', ['title' => 'Senior Laravel Developer']);

        $this->delete("/jobs/{$job->id}")->assertRedirect('/jobs');
        $this->assertDatabaseMissing('job_listings', ['id' => $job->id]);
    }

    public function test_user_books_and_job_tags_relationships(): void
    {
        $user = User::factory()->has(Book::factory()->count(5))->create();
        $this->assertCount(5, $user->books);
        $this->assertTrue($user->books->first()->user->is($user));

        $job = Job::factory()->create();
        $tag = Tag::factory()->create();
        $job->tags()->attach($tag);
        $this->assertCount(1, $job->tags);
    }

    public function test_user_can_register_login_and_logout(): void
    {
        $this->post('/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect('/jobs');

        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'secret123',
        ])->assertRedirect('/jobs');

        $this->assertAuthenticated();
    }
}
