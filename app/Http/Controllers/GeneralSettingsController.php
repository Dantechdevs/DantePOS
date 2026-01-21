<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiteSettingsRequest;
use App\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class GeneralSettingsController extends Controller
{
    /*====================================*/
    public function authenticateRole($module_page)
    {
        $permissionCheck = checkRolePermission($module_page);

        if ($permissionCheck->access == 0) {

            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    public function index()
    {
        $this->authenticateRole($module_page = 'settings');
        $timezones = \DateTimeZone::listIdentifiers();
        $currencies = currencyList();
        $settings = SiteSetting::pluck('value', 'key');
        // $settings = [];
        return view('general_settings.site_settings', compact('settings', 'timezones', 'currencies'));
    }
    /***************************************************************************/
    public function updateSiteSettings(SiteSettingsRequest $request)
    {
        $data = $request->validated();
        foreach ($data as $key => $value) {
            // Handle file uploads
            if (is_file($value)) {
                // Find existing setting
                $existingSetting = SiteSetting::where('key', $key)->first();

                // Unlink the old file if it exists
                if ($existingSetting && $existingSetting->value && file_exists(public_path($existingSetting->value))) {
                    unlink(public_path($existingSetting->value));
                }

                // Upload the new file
                $value = $this->uploadFile($value, 'settings');
            }

            // Update or create the setting
            SiteSetting::updateOrCreate(
                ['key' => $key], // Search for a setting with this key
                [
                    'value' => $value,
                    'createdBy' => auth()->id(), // Set the user ID who updated it
                ]
            );
            if (!empty($data['timezone'])) {
                $this->updateTimezoneConfig($data['timezone']);
            }
        }

        return response()->json(['success' => true, 'message' => 'Settings updated successfully!']);
    }

    private function updateTimezoneConfig($timezone)
    {
        // Validate or fallback
        if (! in_array($timezone, \DateTimeZone::listIdentifiers())) {
            $timezone = 'UTC';
        }
        // $timezone = 'UTC';
        // Write to config/timezone.php
        $configPath = config_path('timezone.php');
        $newContent = "<?php\n\nreturn [\n    'timezone' => '{$timezone}',\n];\n";
        file_put_contents($configPath, $newContent);

        // Clear config and set runtime
        Artisan::call('config:clear');
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }


    /***************************************************************************/
    private function uploadFile($file, $directory)
    {
        // Define the path where the file will be stored inside the `public` directory
        $destinationPath = public_path($directory);

        // Ensure the directory exists, create it if it doesn't
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        // Generate a unique filename for the uploaded file
        $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Move the file to the public directory
        $file->move($destinationPath, $fileName);

        // Return the relative path to the file (for saving in the database)
        return $directory . '/' . $fileName;
    }
}
