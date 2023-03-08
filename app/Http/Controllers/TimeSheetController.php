<?php

namespace App\Http\Controllers;

use App\Repositories\TimesheetRepository;
use Illuminate\Http\Request;

class TimeSheetController extends Controller
{
    public function index()
    {
        $file = fopen(public_path() . '/assets/timesheet[58].csv', 'r');

        $sheetServices = new TimesheetRepository();

        $records = $sheetServices->get_formatted_data($file);

        return view('worksheet', compact('records')); 
    }
}
