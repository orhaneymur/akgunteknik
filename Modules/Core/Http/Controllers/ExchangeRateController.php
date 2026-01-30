<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Models\ExchangeRate;
use Illuminate\Support\Facades\Validator;

class ExchangeRateController extends BaseController
{
    public function index(Request $request)
    {
        $query = ExchangeRate::query();

        // Filter by currency
        if ($request->has('currency')) {
            $query->where('currency', $request->currency);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('rate_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('rate_date', '<=', $request->end_date);
        }

        // Get latest rates for each currency
        if ($request->has('latest') && $request->latest) {
            $currencies = ['USD', 'EUR', 'GBP'];
            $latestRates = [];
            
            foreach ($currencies as $currency) {
                $rate = ExchangeRate::getLatestRate($currency);
                if ($rate) {
                    $latestRates[$currency] = $rate;
                }
            }
            
            return $this->respondSuccess($latestRates, 'Latest exchange rates retrieved successfully.');
        }

        $rates = $query->orderBy('rate_date', 'desc')
            ->orderBy('currency')
            ->paginate(50);

        return $this->respondSuccess($rates, 'Exchange rates retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'required|string|size:3|in:USD,EUR,GBP',
            'rate_date' => 'required|date',
            'rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        // Check if rate already exists for this date and currency
        $exists = ExchangeRate::where('currency', $request->currency)
            ->where('rate_date', $request->rate_date)
            ->first();

        if ($exists) {
            $exists->update([
                'rate' => $request->rate,
                'notes' => $request->notes,
            ]);
            return $this->respondSuccess($exists, 'Exchange rate updated successfully.');
        }

        $rate = ExchangeRate::create([
            'currency' => $request->currency,
            'rate_date' => $request->rate_date,
            'rate' => $request->rate,
            'notes' => $request->notes,
        ]);

        return $this->respondSuccess($rate, 'Exchange rate created successfully.', 201);
    }

    public function getLatest(Request $request, $currency)
    {
        $rate = ExchangeRate::getLatestRate($currency);

        if (!$rate) {
            return $this->respondError([], 'Exchange rate not found for ' . $currency, 404);
        }

        $rateRecord = ExchangeRate::where('currency', $currency)
            ->orderBy('rate_date', 'desc')
            ->first();

        return $this->respondSuccess($rateRecord, 'Latest exchange rate retrieved successfully.');
    }
}
