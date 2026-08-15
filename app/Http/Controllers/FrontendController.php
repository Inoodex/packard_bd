<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class FrontendController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('Employee')) {
            return view('frontend.pages.dashboard_employee', [
                'title' => 'Employee Dashboard',
                'data' => [],
            ]);
        }

        $stats = [
            'totalProducts' => 0,
            'todaysProducts' => 0,
            'thisWeeksProducts' => 0,
            'thisMonthsProducts' => 0,
            'thisYearsProducts' => 0,
            'totalProductValue' => 0,
            'averageProductPrice' => 0,
            'minimumProductPrice' => 0,
            'maximumProductPrice' => 0,
        ];

        if (Schema::hasTable('products')) {
            $stats['totalProducts'] = Product::count();
            $stats['todaysProducts'] = Product::whereDate('created_at', Carbon::today())->count();
            $stats['thisWeeksProducts'] = Product::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
            $stats['thisMonthsProducts'] = Product::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
            $stats['thisYearsProducts'] = Product::whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])->count();
            $stats['totalProductValue'] = Product::sum('price');
            $stats['averageProductPrice'] = Product::avg('price') ?? 0;
            $stats['minimumProductPrice'] = Product::min('price') ?? 0;
            $stats['maximumProductPrice'] = Product::max('price') ?? 0;
        }

        return view('frontend.pages.index', $stats);
    }
}
