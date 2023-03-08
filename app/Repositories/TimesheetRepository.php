<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class TimesheetRepository
{
    public function get_formatted_data($file)
    {
        return $this->format_worksheet_data($file);
    }

    public function format_worksheet_data($file)
    {
        $has_header = true;
        $data = [];
        $worksheet = [];

        while ($csvLine = fgetcsv($file, 1000, ",")) {

            if ($has_header) {
                $has_header = false;
            } else {
                $data[] = [
                    'name' => $csvLine[0],
                    'monday' => $csvLine[1],
                    'tuesday' => $csvLine[2],
                    'wednesday' => $csvLine[3],
                    'thursday' => $csvLine[4],
                    'friday' => $csvLine[5],
                    'saturday' => $csvLine[6],
                    'sunday' => $csvLine[7],
                ];
            }
        }

        foreach($data as $slot) {
            $worksheet[] =  $this->get_individual_data($slot);
        }
        return collect($worksheet)->sortBy('last_name')->toArray();
    }

    public function get_individual_data($data) 
    {
        $ind_data = [];

        if ($data) {
            foreach ($data as $key => $item) {
                if($key == 'name') {
                    $full_name = explode(' ', $item);
                    $ind_data['first_name'] = $full_name[0];
                    $ind_data['last_name'] = $full_name[1];
                } else {
                    $ind_data['work_stat'] = $this->get_work_stat($data);
                }
            }
        }

        return $ind_data;
    }

    public function get_work_stat($data)
    {
        $regular_hour = 8;

        $overtime = [];
        $regular_time = [];
        $double_time = [];
        $no_of_days_worked = 0;

        if ($data) {
            foreach ($data as $key => $item) {
                if($key !== 'name' && $item ) {
                    // total days worked per week
                    $no_of_days_worked ++;

                    $total_daily_hours = $this->get_total_daily_hours_worked($item) - 1; // less 1 hour break

                    // weekday calculation
                    if (!in_array($key, ['saturday', 'sunday'])) {

                        // Work over 8 hours but up to 12 hours is considered overtime
                        if($total_daily_hours > $regular_hour && $total_daily_hours >= 12) {
                            array_push($overtime, ($total_daily_hours - $regular_hour));
                        }

                        // The first 8 working hours of the day are regular time.
                        if($total_daily_hours <= $regular_hour) {
                            array_push($regular_time, $total_daily_hours);
                        }

                        // Work over 12 hours in a single day is considered double time.
                        if($total_daily_hours >= 12) {
                            array_push($double_time, $total_daily_hours);
                        }
                    } 

                    // weekend calculation
                    if (in_array($key, ['saturday', 'sunday'])) {
                        //  If someone works for 7 consecutive days the rules on the 7th day change as follows:
                        if($no_of_days_worked === 7 && $key === 'sunday') {
                            // The first 8 hours of work are always considered overtime.
                            if($total_daily_hours > $regular_hour) {
                                array_push($overtime, $regular_hour);
                            }
        
                            // The hours after the first 8 are considered double time.
                            if($total_daily_hours > $regular_hour) {
                                array_push($double_time, $total_daily_hours - $regular_hour);
                            }
                        } else {
                            // The first 8 hours of work are always considered overtime.
                            if($total_daily_hours > $regular_hour) {
                                array_push($overtime, $regular_hour);
                            }
        
                            // The hours after the first 8 are considered double time.
                            if($total_daily_hours > $regular_hour) {
                                array_push($double_time, $total_daily_hours - $regular_hour);
                            }
                        }
                        
                    }
                }
            }
        }

        $total_overtime = array_sum($overtime);
        $total_regular_time = array_sum($regular_time);
        $total_double_time = array_sum($double_time);
        $total_paid_hours = array_sum([$total_regular_time, $total_overtime, $total_double_time]);

        return [
            'total_overtime' => $total_overtime,
            'total_regular_time' => $total_regular_time,
            'total_double_time' => $total_double_time,
            'total_no_of_days_worked' => $no_of_days_worked,
            'total_paid_hours' => $total_paid_hours,
            'worked_on_weekends' => $no_of_days_worked == 7 ? 2 : ($no_of_days_worked == 6 ? 1 : 0),
            'worked_upto_40_hours' => $total_paid_hours >= 40 ? true : false
        ];
    }

    public function get_total_daily_hours_worked($time)
    {
        if($time) {
            $hours = explode('-', $time);
            $start = Carbon::createFromFormat('H:s', $hours[0]);
            $end = Carbon::createFromFormat('H:s', $hours[1]);
            $diff_in_hours = $end->diffInHours($start);
        } else {
            $diff_in_hours = 0;
        }

        return $diff_in_hours;
    }
}