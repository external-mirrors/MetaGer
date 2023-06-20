<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class MembershipController extends Controller
{
    /**
     * First stage of membership form
     * gather information for contact data
     */
    public function contactData(Request $request)
    {
        return response(view("membership", ["title" => __("titles.membership"), "css" => [mix("/css/membership.css")], "js" => [mix("/js/membership.js")]]));
    }

    public function submitMembershipForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => 'required'
        ]);
        if ($validator->fails()) {
            return response(
                view(
                    "membership",
                    [
                        "title" => __("titles.membership"),
                        "css" => [mix("/css/membership.css")],
                        "js" => [mix("/js/membership.js")]
                    ]
                )
            );
        }
    }
}