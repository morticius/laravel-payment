<?php

namespace App\Http\Controllers;

use App\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentsController extends Controller
{
    public function create()
    {
        return view('payments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|integer|min:1',
            'currency' => 'required'
        ]);

        $request->user()->payments()->create([
            'email' => $request->email,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'name' => $request->name,
            'description' => $request->description,
            'message' => $request->message,
        ]);
    }
}
