<?php
declare(strict_types=1);

use App\Service\ReportService;
use PHPUnit\Framework\TestCase;

final class ReportServiceTest extends TestCase
{
    public function testResolveNoticeTypesForTab(): void
    {
        $this->assertSame([8, 15, 16, 17], ReportService::resolveNoticeTypesForTab(1, true));
        $this->assertSame([10, 11, 12, 13], ReportService::resolveNoticeTypesForTab(2, true));
        $this->assertSame([1, 2, 3, 4, 5, 6, 7], ReportService::resolveNoticeTypesForTab(3, true));
        $this->assertSame([0, 18, 19, 20, 21], ReportService::resolveNoticeTypesForTab(4, true));
        $this->assertSame([9], ReportService::resolveNoticeTypesForTab(5, true));
        $this->assertNull(ReportService::resolveNoticeTypesForTab(5, false));
        $this->assertNull(ReportService::resolveNoticeTypesForTab(999, true));
    }

    public function testMapReportTypeMatchesLegacyRules(): void
    {
        $this->assertSame(1, ReportService::mapReportType(2));
        $this->assertSame(1, ReportService::mapReportType(21));
        $this->assertSame(10, ReportService::mapReportType(14));
        $this->assertSame(15, ReportService::mapReportType(16));
        $this->assertSame(3, ReportService::mapReportType(19));
        $this->assertSame(22, ReportService::mapReportType(23));
        $this->assertSame(8, ReportService::mapReportType(8));
    }

    public function testFilterNoticesByType(): void
    {
        $notices = [
            ['id' => 1, 'ntype' => 8],
            ['id' => 2, 'ntype' => 9],
            ['id' => 3, 'ntype' => 8],
        ];

        $filtered = ReportService::filterNoticesByType($notices, [8]);

        $this->assertSame(
            [
                ['id' => 1, 'ntype' => 8],
                ['id' => 3, 'ntype' => 8],
            ],
            $filtered,
        );
    }
}

