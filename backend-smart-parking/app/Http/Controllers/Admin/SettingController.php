<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::firstOrCreate([], ['parking_capacity' => 0]);

        return view('admin.setting', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'parking_capacity' => 'required|integer|min:1'
        ]);

        $setting = Setting::first();

        if (!$setting) {
            $setting = new Setting();
        }

        $setting->parking_capacity = $request->parking_capacity;
        $setting->save();

        return redirect()
            ->route('admin.setting')
            ->with('success', 'Parking capacity updated successfully!');
    }
}
