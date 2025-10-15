<?php

namespace Tests\Unit\Services;

use App\Models\Inquiry;
use App\Services\InquiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InquiryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InquiryService();
    }

    public function test_get_inquiries_returns_paginated_results(): void
    {
        Inquiry::factory()->count(20)->create();

        $result = $this->service->getInquiries(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }

    public function test_get_inquiries_filters_by_search(): void
    {
        Inquiry::factory()->create(['email' => 'john@example.com']);
        Inquiry::factory()->create(['email' => 'jane@example.com']);
        Inquiry::factory()->create(['email' => 'bob@other.com']);

        $result = $this->service->getInquiries(['search' => 'example']);

        $this->assertCount(2, $result->items());
    }

    public function test_get_inquiries_sorts_correctly(): void
    {
        Inquiry::factory()->create(['email' => 'charlie@test.com']);
        Inquiry::factory()->create(['email' => 'alice@test.com']);
        Inquiry::factory()->create(['email' => 'bob@test.com']);

        $result = $this->service->getInquiries([
            'sort' => 'email',
            'direction' => 'asc',
        ]);

        $emails = $result->pluck('email')->toArray();
        $this->assertEquals('alice@test.com', $emails[0]);
        $this->assertEquals('bob@test.com', $emails[1]);
        $this->assertEquals('charlie@test.com', $emails[2]);
    }

    public function test_delete_inquiry_removes_record(): void
    {
        $inquiry = Inquiry::factory()->create();

        $result = $this->service->deleteInquiry($inquiry->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
    }

    public function test_get_inquiry_stats_returns_correct_counts(): void
    {
        // Create inquiries for different time periods
        Inquiry::factory()->create(['created_at' => now()]);
        Inquiry::factory()->create(['created_at' => now()->subDays(2)]);
        Inquiry::factory()->create(['created_at' => now()->subMonth()]);

        $stats = $this->service->getInquiryStats();

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(1, $stats['today']);
    }

    public function test_search_inquiries_returns_matching_results(): void
    {
        Inquiry::factory()->create(['email' => 'test@example.com']);
        Inquiry::factory()->create(['email' => 'test@test.com']);
        Inquiry::factory()->create(['email' => 'other@example.com']);

        $result = $this->service->searchInquiries('test');

        $this->assertCount(2, $result->items());
    }

    public function test_get_inquiries_prevents_sql_injection_in_sort(): void
    {
        Inquiry::factory()->count(3)->create();

        // Try to inject SQL
        $result = $this->service->getInquiries([
            'sort' => 'email; DROP TABLE inquiries--',
            'direction' => 'asc',
        ]);

        // Should still return results without error
        $this->assertGreaterThan(0, $result->total());
    }
}

