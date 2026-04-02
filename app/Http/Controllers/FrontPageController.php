<?php

namespace App\Http\Controllers;

use App\Models\TPackage;
use Illuminate\Contracts\View\View;

class FrontPageController extends Controller
{
    public function home(): View
    {
        return view('pages.home');
    }

    public function packages(): View
    {
        $packages = TPackage::query()
            ->where('status', 1)
            ->orderBy('sort')
            ->get()
            ->map(function (TPackage $package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'code' => $package->code,
                    'price' => $package->price,
                    'year_price' => $package->year_price,
                    'features' => $package->features_array,
                    'trial_days' => $package->trial_days,
                    'is_featured' => $package->code === 'pro',
                ];
            });

        return view('pages.packages', [
            'packages' => $packages,
        ]);
    }
}
