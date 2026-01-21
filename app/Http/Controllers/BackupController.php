<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        return view('backup.index');
    }
    public function backup()
    {
        // Fetch all data from the database
        $tables = [
            'advance_histories',
            'areas',
            'customers',
            'customer_opening_balances',
            'employees',
            'employee_return_advances',
            'expenses',
            'expense_categories',
            'monthly_salaries',
            'password_resets',
            'products',
            'purchases',
            'sales',
            'suppliers',
            'supplier_payments',
            'units',
            'users',
        ];
        // Add all your table names here
        $backupData = [];

        foreach ($tables as $table) {
            $backupData[$table] = DB::table($table)->get()->toArray();
        }

        // Save the backup as a JSON file
        $fileName = 'backup_' . now()->format('Y_m_d_H_i_s') . '.json';
        Storage::disk('local')->put($fileName, json_encode($backupData));

        return response()->download(storage_path("app/{$fileName}"))->deleteFileAfterSend();
    }

    public function restore(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'backup_file' => 'required|file|mimes:json',
        ]);

        // Parse the uploaded JSON file
        $fileContent = file_get_contents($request->file('backup_file')->getRealPath());
        $backupData = json_decode($fileContent, true);

        // Merge data into the database
        foreach ($backupData as $table => $records) {
            foreach ($records as $record) {
                // Check for conflicts and merge data
                $existingRecord = DB::table($table)->where('id', $record['id'])->first();
                // if (!$existingRecord) {
                //     DB::table('users')->insert($record);
                // }
                if ($existingRecord) {
                    //     // Update existing record or skip (customize as needed)
                    DB::table($table)->where('id', $record['id'])->update((array)$record);
                    //     unset($record['id']);

                    //     // Insert the record as a new entry with a new ID
                    //     DB::table($table)->insert((array)$record);
                } else {
                    //     // Insert new record
                    DB::table($table)->insert((array)$record);
                }
            }
        }
        dd('restored');
        return response()->json(['message' => 'Data restored and merged successfully!']);
    }
}
