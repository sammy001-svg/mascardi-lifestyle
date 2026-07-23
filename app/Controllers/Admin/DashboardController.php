<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;
use App\Models\ActivityLog;

final class DashboardController
{
    public function index(): void
    {
        $pdo = Database::connection();

        $pillarCount = (int) $pdo->query('SELECT COUNT(*) FROM pillars')->fetchColumn();
        $partnerCount = (int) $pdo->query('SELECT COUNT(*) FROM partners')->fetchColumn();
        $activePillarCount = (int) $pdo->query('SELECT COUNT(*) FROM pillars WHERE is_active = 1')->fetchColumn();

        // These tables gain real data in later phases — 0 today is expected, not an error.
        $orderCount = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        $eventCount = (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();

        View::render('admin/dashboard/index', [
            'pageTitle' => 'Dashboard',
            'pageSubtitle' => 'Overview of your Mascardi Lifestyle ecosystem',
            'activeModule' => 'dashboard',
            'pillarCount' => $pillarCount,
            'activePillarCount' => $activePillarCount,
            'partnerCount' => $partnerCount,
            'orderCount' => $orderCount,
            'eventCount' => $eventCount,
            'recentActivity' => ActivityLog::recent(8),
        ]);
    }
}
