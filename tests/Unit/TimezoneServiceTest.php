<?php

namespace Tests\Unit;

use App\Services\TimezoneService;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for TimezoneService.
 * 
 * These tests verify the timezone service methods work correctly
 * in isolation without requiring database or application state.
 */
class TimezoneServiceTest extends TestCase
{
    private TimezoneService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TimezoneService();
    }

    /**
     * Test that getFlatTimezones returns an array.
     */
    public function test_get_flat_timezones_returns_array(): void
    {
        $result = $this->service->getFlatTimezones();
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * Test that flat timezones contain expected entries.
     */
    public function test_flat_timezones_contain_expected_entries(): void
    {
        $result = $this->service->getFlatTimezones();
        
        $this->assertArrayHasKey('UTC', $result);
        $this->assertArrayHasKey('America/New_York', $result);
        $this->assertArrayHasKey('Europe/London', $result);
        $this->assertArrayHasKey('Asia/Tokyo', $result);
    }

    /**
     * Test that flat timezones exclude deprecated ones.
     */
    public function test_flat_timezones_exclude_deprecated(): void
    {
        $result = $this->service->getFlatTimezones();
        
        // Should not contain Etc/ timezones
        $etcTimezones = array_filter(array_keys($result), fn($tz) => str_starts_with($tz, 'Etc/'));
        $this->assertEmpty($etcTimezones);
        
        // Should not contain SystemV/ timezones
        $systemVTimezones = array_filter(array_keys($result), fn($tz) => str_starts_with($tz, 'SystemV/'));
        $this->assertEmpty($systemVTimezones);
    }

    /**
     * Test that getGroupedTimezones returns properly structured array.
     */
    public function test_grouped_timezones_structure(): void
    {
        $result = $this->service->getGroupedTimezones();
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Check major regions exist
        $this->assertArrayHasKey('America', $result);
        $this->assertArrayHasKey('Europe', $result);
        $this->assertArrayHasKey('Asia', $result);
        $this->assertArrayHasKey('Africa', $result);
        $this->assertArrayHasKey('Pacific', $result);
        
        // Verify structure of each region
        foreach ($result as $region => $timezones) {
            $this->assertIsString($region);
            $this->assertIsArray($timezones);
            $this->assertNotEmpty($timezones);
            
            // Each timezone should have identifier => label
            foreach ($timezones as $identifier => $label) {
                $this->assertIsString($identifier);
                $this->assertIsString($label);
                $this->assertStringContainsString($region, $identifier);
            }
        }
    }

    /**
     * Test timezone validation with valid timezones.
     */
    public function test_is_valid_with_valid_timezones(): void
    {
        $validTimezones = [
            'UTC',
            'America/New_York',
            'Europe/London',
            'Asia/Tokyo',
            'Australia/Sydney',
            'Pacific/Auckland',
        ];
        
        foreach ($validTimezones as $timezone) {
            $this->assertTrue(
                $this->service->isValid($timezone),
                "Expected {$timezone} to be valid"
            );
        }
    }

    /**
     * Test timezone validation with invalid timezones.
     */
    public function test_is_valid_with_invalid_timezones(): void
    {
        $invalidTimezones = [
            'Invalid/Timezone',
            'Not/A/Real/Place',
            '',
            'RandomString',
            'America/NotACity',
        ];
        
        foreach ($invalidTimezones as $timezone) {
            $this->assertFalse(
                $this->service->isValid($timezone),
                "Expected {$timezone} to be invalid"
            );
        }
    }

    /**
     * Test getOffsetHours for UTC.
     */
    public function test_get_offset_hours_for_utc(): void
    {
        $offset = $this->service->getOffsetHours('UTC');
        
        $this->assertIsInt($offset);
        $this->assertEquals(0, $offset);
    }

    /**
     * Test getOffsetHours returns reasonable values.
     */
    public function test_get_offset_hours_returns_reasonable_values(): void
    {
        $testTimezones = [
            'America/New_York' => [-5, -4], // EST/EDT
            'Europe/London' => [0, 1],      // GMT/BST
            'Asia/Tokyo' => [9, 9],         // JST (no DST)
            'Australia/Sydney' => [10, 11],  // AEST/AEDT
        ];
        
        foreach ($testTimezones as $timezone => $expectedRange) {
            $offset = $this->service->getOffsetHours($timezone);
            
            $this->assertIsInt($offset);
            $this->assertGreaterThanOrEqual(
                $expectedRange[0],
                $offset,
                "Offset for {$timezone} should be >= {$expectedRange[0]}"
            );
            $this->assertLessThanOrEqual(
                $expectedRange[1],
                $offset,
                "Offset for {$timezone} should be <= {$expectedRange[1]}"
            );
        }
    }

    /**
     * Test getOffsetHours with invalid timezone.
     */
    public function test_get_offset_hours_with_invalid_timezone(): void
    {
        $offset = $this->service->getOffsetHours('Invalid/Timezone');
        
        $this->assertIsInt($offset);
        $this->assertEquals(0, $offset); // Should default to 0
    }

    /**
     * Test getCommonTimezones returns expected timezones.
     */
    public function test_get_common_timezones(): void
    {
        $result = $this->service->getCommonTimezones();
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Check expected common timezones (based on actual implementation - USA, Canada, and key international)
        $expectedCommon = [
            'UTC',
            'America/New_York',
            'America/Los_Angeles',
            'America/Chicago',
            'America/Toronto',
            'America/Vancouver',
        ];
        
        foreach ($expectedCommon as $timezone) {
            $this->assertArrayHasKey($timezone, $result);
        }
        
        // Common list should focus on North America and key international (< 20)
        $this->assertLessThan(20, count($result));
        
        // Ensure it's not empty
        $this->assertNotEmpty($result);
    }

    /**
     * Test that common timezones have formatted labels.
     */
    public function test_common_timezones_have_labels(): void
    {
        $result = $this->service->getCommonTimezones();
        
        foreach ($result as $identifier => $label) {
            $this->assertIsString($identifier);
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
            
            // Label should contain offset information (GMT±X)
            $this->assertMatchesRegularExpression('/GMT[+-]\d+/', $label);
        }
    }
}

