<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LaravelLocalization;
use Illuminate\Support\Facades\Validator;

class DonationController extends Controller
{
    function amount(Request $request)
    {
        return view('spende.amount')

            ->with('title', trans('titles.spende'))
            ->with('css', [mix('/css/spende.css')])
            ->with('darkcss', [mix('/css/spende-dark.css')])
            ->with('js', [mix('/js/donation.js')])
            ->with('navbarFocus', 'foerdern');
    }

    function interval(Request $request, $amount)
    {
        $validator = Validator::make(["amount" => $amount], [
            'amount' => 'required|decimal:2|min:1'
        ]);
        if ($validator->fails()) {
            return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende'));
        }
        return view('spende.interval')
            ->with('donation', [
                "amount" => $amount
            ])
            ->with('title', trans('titles.spende'))
            ->with('css', [mix('/css/spende.css')])
            ->with('darkcss', [mix('/css/spende-dark.css')])
            ->with('js', [mix('/js/donation.js')]);
    }
}