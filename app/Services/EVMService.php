<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class EVMService
{
    /**
     * Get the cumulative PV and EV over time for a project using Postgres Window Functions.
     */
    public function getSCurveData(Project $project)
    {
        $projectId = $project->id;

        // 1. Calculate PV (Planned Value) over time
        // Using generate_series to create a date range from project start to end,
        // then calculating linear daily PV for each task, and summing it up cumulatively.
        $pvQuery = "
            WITH date_series AS (
                SELECT generate_series(
                    COALESCE((SELECT MIN(start_date) FROM tasks WHERE project_id = :project_id_1 AND deleted_at IS NULL), CURRENT_DATE),
                    COALESCE((SELECT MAX(end_date) FROM tasks WHERE project_id = :project_id_2 AND deleted_at IS NULL), CURRENT_DATE),
                    '1 day'::interval
                )::date AS date
            ),
            task_daily_pv AS (
                SELECT 
                    d.date,
                    t.id,
                    -- Daily weight = total weight / duration in days (min 1 day to avoid div by zero)
                    t.weight / GREATEST((t.end_date - t.start_date), 1) AS daily_weight
                FROM date_series d
                JOIN tasks t ON t.project_id = :project_id_3 AND t.deleted_at IS NULL 
                     AND d.date > t.start_date AND d.date <= t.end_date
            ),
            pv_grouped AS (
                SELECT 
                    date, 
                    SUM(daily_weight) AS daily_pv
                FROM task_daily_pv
                GROUP BY date
            )
            SELECT 
                TO_CHAR(date, 'YYYY-MM-DD') as date,
                ROUND(SUM(daily_pv) OVER (ORDER BY date)::numeric, 2) AS cumulative_pv
            FROM pv_grouped
        ";

        $pvData = DB::select($pvQuery, [
            'project_id_1' => $projectId,
            'project_id_2' => $projectId,
            'project_id_3' => $projectId
        ]);

        // 2. Calculate EV (Earned Value) over time
        // Task progress is absolute, so we find the increment per report using LAG()
        // Then we multiply by task weight to get EV increment, and sum cumulatively.
        $evQuery = "
            WITH progress_increments AS (
                SELECT 
                    tp.task_id,
                    tp.report_date,
                    tp.progress_percent - COALESCE(
                        LAG(tp.progress_percent) OVER (PARTITION BY tp.task_id ORDER BY tp.report_date), 
                        0
                    ) AS progress_increment
                FROM task_progress tp
                JOIN tasks t ON t.id = tp.task_id
                WHERE t.project_id = :project_id_1 AND t.deleted_at IS NULL
            ),
            ev_daily AS (
                SELECT 
                    pi.report_date,
                    SUM(pi.progress_increment * t.weight / 100.0) AS daily_ev
                FROM progress_increments pi
                JOIN tasks t ON t.id = pi.task_id
                GROUP BY pi.report_date
            )
            SELECT 
                TO_CHAR(report_date, 'YYYY-MM-DD') as date,
                ROUND(SUM(daily_ev) OVER (ORDER BY report_date)::numeric, 2) AS cumulative_ev
            FROM ev_daily
        ";

        $evData = DB::select($evQuery, ['project_id_1' => $projectId]);

        // Merge the two datasets by date
        $merged = [];
        
        foreach ($pvData as $pv) {
            $merged[$pv->date] = [
                'date' => $pv->date,
                'pv' => (float) $pv->cumulative_pv,
                'ev' => null
            ];
        }

        $lastEv = 0;
        foreach ($evData as $ev) {
            if (!isset($merged[$ev->date])) {
                $merged[$ev->date] = [
                    'date' => $ev->date,
                    'pv' => null, // Will interpolate or carry forward later if needed
                    'ev' => (float) $ev->cumulative_ev
                ];
            } else {
                $merged[$ev->date]['ev'] = (float) $ev->cumulative_ev;
            }
            $lastEv = (float) $ev->cumulative_ev;
        }

        // Sort by date
        ksort($merged);

        // Fill forward missing PV and EV values to create continuous lines
        $currentPv = 0;
        $currentEv = null; // Don't start EV until the first report
        $results = [];

        foreach ($merged as $date => $data) {
            if ($data['pv'] !== null) {
                $currentPv = $data['pv'];
            } else {
                $data['pv'] = $currentPv;
            }

            if ($data['ev'] !== null) {
                $currentEv = $data['ev'];
            } elseif ($currentEv !== null) {
                $data['ev'] = $currentEv;
            }

            $results[] = [
                'date' => $date,
                'pv' => $data['pv'],
                'ev' => $data['ev']
            ];
        }

        return $results;
    }

    /**
     * Calculate Project Health based on SPI
     */
    public function getProjectHealth(Project $project)
    {
        $data = $this->getSCurveData($project);
        
        if (empty($data)) {
            return [
                'spi' => 0,
                'status' => 'No Data',
                'color' => 'secondary'
            ];
        }

        // Get the latest date that has EV
        $latestWithEv = null;
        foreach (array_reverse($data) as $row) {
            if ($row['ev'] !== null) {
                $latestWithEv = $row;
                break;
            }
        }

        if (!$latestWithEv || $latestWithEv['pv'] == 0) {
            return [
                'spi' => 0,
                'status' => 'Planning',
                'color' => 'info'
            ];
        }

        $spi = $latestWithEv['ev'] / $latestWithEv['pv'];
        
        if ($spi >= 0.95) {
            $status = 'On Track';
            $color = 'success';
        } elseif ($spi >= 0.85) {
            $status = 'At Risk';
            $color = 'warning';
        } else {
            $status = 'Delayed';
            $color = 'danger';
        }

        return [
            'spi' => round($spi, 2),
            'status' => $status,
            'color' => $color
        ];
    }
}
