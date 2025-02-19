<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;

class DataController extends Controller
{
    public function index(Request $request)
    {
        $email = "fauzidwiseptiawan123@gmail.com";
        $response = Http::get("https://bsby.siglab.co.id/api/test-programmer", [
            'email' => $email
        ])->json();

        // Ambil data dari "results"
        $data = collect($response['results'] ?? []);

        // Mapping Type ke Title
        $typeReference = [
            1 => 'Food & Beverage',
            2 => 'Pharmaceulicals',
            3 => 'Government',
            4 => 'Traditional Medicine & Suplement',
            13 => 'Beauty, Cosmetics & Personal Care',
            14 => 'Media RTU',
            15 => 'K3L Products',
            16 => 'ALKES & PKRT',
            17 => 'Feed, Pesticides & PSAT',
            18 => 'Other',
            19 => 'Research / Academic Purpose',
            20 => 'Dioxine Udara'
        ];

        $data = $data->map(function ($item) use ($typeReference) {
            $item['title'] = $typeReference[$item['type']] ?? 'Unknown';
            return $item;
        });

        // Filtering
        if ($request->has('status')) {
            $data = $data->where('status', (int) $request->input('status'))->values();
        }
        if ($request->has('attachment')) {
            $data = $data->where('attachment', (int) $request->input('attachment'))->values();
        }
        if ($request->has('discount')) {
            if ($request->input('discount') == 'yes') {
                $data = $data->where('discount', '>', 0)->values();
            } else {
                $data = $data->where('discount', 0)->values();
            }
        }

        // Sorting jika ada
        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by');
            $order = $request->input('order') === 'desc';
            $data = $data->sortBy($sortBy, SORT_REGULAR, $order)->values();
        }

        // PAGINATION
        $perPage = 10;
        $currentPage = $request->input('page', 1); // Ambil page dari request
        $currentItems = $data->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $pagination = new LengthAwarePaginator(
            $currentItems,
            $data->count(),
            $perPage,
            $currentPage,
            ['path' => url('/data')]
        );

        return response()->json($pagination);
    }
}
