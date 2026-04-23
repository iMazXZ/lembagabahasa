<?php

namespace Tests\Unit;

use App\Models\EptOnlineResult;
use App\Models\EptOnlineSection;
use PHPUnit\Framework\TestCase;

class EptOnlineResultScoreConversionTest extends TestCase
{
    public function test_listening_score_is_scaled_using_conversion_table(): void
    {
        $this->assertSame(68, EptOnlineResult::scaleSectionScore(EptOnlineSection::TYPE_LISTENING, 50));
        $this->assertSame(57, EptOnlineResult::scaleSectionScore(EptOnlineSection::TYPE_LISTENING, 40));
        $this->assertSame(24, EptOnlineResult::scaleSectionScore(EptOnlineSection::TYPE_LISTENING, 0));
    }

    public function test_structure_score_is_scaled_using_conversion_table(): void
    {
        $this->assertSame(68, EptOnlineResult::scaleSectionScore(EptOnlineSection::TYPE_STRUCTURE, 40));
        $this->assertSame(50, EptOnlineResult::scaleSectionScore(EptOnlineSection::TYPE_STRUCTURE, 26));
        $this->assertSame(20, EptOnlineResult::scaleSectionScore(EptOnlineSection::TYPE_STRUCTURE, 0));
    }

    public function test_reading_score_is_scaled_using_conversion_table(): void
    {
        $this->assertSame(67, EptOnlineResult::scaleSectionScore(EptOnlineSection::TYPE_READING, 50));
        $this->assertSame(55, EptOnlineResult::scaleSectionScore(EptOnlineSection::TYPE_READING, 40));
        $this->assertSame(21, EptOnlineResult::scaleSectionScore(EptOnlineSection::TYPE_READING, 0));
    }

    public function test_total_scaled_is_calculated_from_three_section_scores(): void
    {
        $this->assertSame(677, EptOnlineResult::calculateTotalScaled(68, 68, 67));
        $this->assertSame(480, EptOnlineResult::calculateTotalScaled(51, 45, 48));
    }

    public function test_total_scaled_returns_null_if_any_section_is_missing(): void
    {
        $this->assertNull(EptOnlineResult::calculateTotalScaled(68, null, 67));
    }
}
