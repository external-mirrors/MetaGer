<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MembershipController extends Controller
{
    /**
     * First stage of membership form
     * gather information for contact data
     */
    public function contactData(Request $request)
    {
        return response(view("membership.contact", ["title" => __("titles.membership")]));
    }
}