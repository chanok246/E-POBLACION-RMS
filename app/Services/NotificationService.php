<?php

namespace App\Services;

use App\Models\ReliefAssistanceOperation;
use App\Models\ReportComplaint;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NotificationService
{
    /*
    |--------------------------------------------------------------------------
    | Get Notification Data
    |--------------------------------------------------------------------------
    |
    | Collects operational reminders for:
    |
    | 1. Relief operations happening today
    | 2. Relief operations happening tomorrow
    | 3. Person complaint hearings happening today
    | 4. Person complaint hearings happening tomorrow
    |
    | This first version is intentionally dynamic.
    | It reads the existing records directly and does not require
    | a notifications database table.
    |
    */

    public function getNotifications(): array
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $todayNotifications = collect();
        $tomorrowNotifications = collect();

        /*
        |--------------------------------------------------------------------------
        | RELIEF OPERATIONS
        |--------------------------------------------------------------------------
        */

        $operations = ReliefAssistanceOperation::query()
            ->whereNotNull('distribution_date')
            ->whereNotIn('status', [
                'completed',
                'Completed',
                'cancelled',
                'Cancelled',
            ])
            ->orderBy('distribution_date')
            ->orderBy('distribution_time')
            ->get();

        foreach ($operations as $operation) {

            $distributionDate = Carbon::parse(
                $operation->distribution_date
            );

            /*
            |--------------------------------------------------------------------------
            | TODAY
            |--------------------------------------------------------------------------
            */

            if ($distributionDate->isSameDay($today)) {

                $todayNotifications->push(
                    $this->makeReliefNotification(
                        $operation,
                        'today'
                    )
                );
            }

            /*
            |--------------------------------------------------------------------------
            | TOMORROW
            |--------------------------------------------------------------------------
            */

            if ($distributionDate->isSameDay($tomorrow)) {

                $tomorrowNotifications->push(
                    $this->makeReliefNotification(
                        $operation,
                        'tomorrow'
                    )
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PERSON COMPLAINT HEARINGS
        |--------------------------------------------------------------------------
        */

        $complaints = ReportComplaint::query()
            ->where('report_type', 'person')
            ->whereNotNull('hearing_date')
            ->whereNotIn('status', [
                'Closed',
                'Disapproved',
            ])
            ->orderBy('hearing_date')
            ->orderBy('hearing_time')
            ->get();

        foreach ($complaints as $complaint) {

            $hearingDate = Carbon::parse(
                $complaint->hearing_date
            );

            /*
            |--------------------------------------------------------------------------
            | TODAY
            |--------------------------------------------------------------------------
            */

            if ($hearingDate->isSameDay($today)) {

                $todayNotifications->push(
                    $this->makeComplaintNotification(
                        $complaint,
                        'today'
                    )
                );
            }

            /*
            |--------------------------------------------------------------------------
            | TOMORROW
            |--------------------------------------------------------------------------
            */

            if ($hearingDate->isSameDay($tomorrow)) {

                $tomorrowNotifications->push(
                    $this->makeComplaintNotification(
                        $complaint,
                        'tomorrow'
                    )
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SORT NOTIFICATIONS
        |--------------------------------------------------------------------------
        |
        | Keep today's reminders first, followed by tomorrow's reminders.
        |
        */

        $todayNotifications = $todayNotifications
            ->sortBy('sort_time')
            ->values();

        $tomorrowNotifications = $tomorrowNotifications
            ->sortBy('sort_time')
            ->values();


        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return [
            'count' => $todayNotifications->count()
                + $tomorrowNotifications->count(),

            'today' => $todayNotifications,

            'tomorrow' => $tomorrowNotifications,

            'has_today' => $todayNotifications->isNotEmpty(),

            'has_tomorrow' => $tomorrowNotifications->isNotEmpty(),

            'has_notifications' =>
                $todayNotifications->isNotEmpty()
                || $tomorrowNotifications->isNotEmpty(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | RELIEF NOTIFICATION
    |--------------------------------------------------------------------------
    */

    private function makeReliefNotification(
        ReliefAssistanceOperation $operation,
        string $period
    ): array {

        $distributionDate = Carbon::parse(
            $operation->distribution_date
        );

        $time = $this->formatTime(
            $operation->distribution_time
        );

        $location = trim(
            (string) $operation->distribution_location
        );

        $operationName = trim(
            (string) $operation->operation_name
        );

        if ($operationName === '') {
            $operationName = 'Relief Assistance Operation';
        }

        if ($period === 'today') {

            $title = 'Relief Operation Today';

            $icon = 'bi-exclamation-circle-fill';

            $priority = 'urgent';

        } else {

            $title = 'Relief Operation Tomorrow';

            $icon = 'bi-calendar-event';

            $priority = 'upcoming';
        }


        return [
            'type' => 'relief_operation',

            'period' => $period,

            'title' => $title,

            'message' => $operationName,

            'description' =>
                $this->buildScheduleText(
                    $distributionDate,
                    $time,
                    $location
                ),

            'icon' => $icon,

            'priority' => $priority,

            'date' => $distributionDate->format('Y-m-d'),

            'display_date' =>
                $distributionDate->format('F j, Y'),

            'time' => $time,

            'location' =>
                $location !== ''
                    ? $location
                    : 'Location not specified',

            'record_id' => $operation->id,

            'operation_code' =>
                $operation->operation_code,

            'sort_time' =>
                $distributionDate->format('Y-m-d')
                . ' '
                . ($operation->distribution_time ?? '00:00:00'),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PERSON COMPLAINT NOTIFICATION
    |--------------------------------------------------------------------------
    */

    private function makeComplaintNotification(
        ReportComplaint $complaint,
        string $period
    ): array {

        $hearingDate = Carbon::parse(
            $complaint->hearing_date
        );

        $time = $this->formatTime(
            $complaint->hearing_time
        );

        $location = trim(
            (string) $complaint->hearing_location
        );

        $recordCode = trim(
            (string) $complaint->record_code
        );

        if ($recordCode === '') {
            $recordCode = 'Person Complaint';
        }


        if ($period === 'today') {

            $title = 'Hearing Today';

            $icon = 'bi-exclamation-circle-fill';

            $priority = 'urgent';

        } else {

            $title = 'Person Complaint Hearing Tomorrow';

            $icon = 'bi-calendar-event';

            $priority = 'upcoming';
        }


        return [
            'type' => 'person_complaint_hearing',

            'period' => $period,

            'title' => $title,

            'message' =>
                'Hearing for complaint '
                . $recordCode,

            'description' =>
                $this->buildScheduleText(
                    $hearingDate,
                    $time,
                    $location
                ),

            'icon' => $icon,

            'priority' => $priority,

            'date' => $hearingDate->format('Y-m-d'),

            'display_date' =>
                $hearingDate->format('F j, Y'),

            'time' => $time,

            'location' =>
                $location !== ''
                    ? $location
                    : 'Location not specified',

            'record_id' => $complaint->id,

            'record_code' => $recordCode,

            'sort_time' =>
                $hearingDate->format('Y-m-d')
                . ' '
                . ($complaint->hearing_time ?? '00:00:00'),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | SCHEDULE TEXT
    |--------------------------------------------------------------------------
    */

    private function buildScheduleText(
        Carbon $date,
        string $time,
        string $location
    ): string {

        $parts = [];

        $parts[] = $date->format('F j, Y');

        if ($time !== '') {
            $parts[] = $time;
        }

        if ($location !== '') {
            $parts[] = $location;
        }

        return implode(' • ', $parts);
    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT TIME
    |--------------------------------------------------------------------------
    */

    private function formatTime(?string $time): string
    {
        if (
            $time === null
            || trim((string) $time) === ''
        ) {
            return '';
        }

        try {

            return Carbon::parse(
                (string) $time
            )->format('g:i A');

        } catch (\Throwable $e) {

            return trim(
                (string) $time
            );
        }
    }
}