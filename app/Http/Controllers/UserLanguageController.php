<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class UserLanguageController extends Controller
{


    public function update(Request $request)
    {

        $request->validate([

            'language'=>'required|in:en,hi,gu'

        ]);


        // For this project without authentication
        $user = User::first();


        if(!$user)
        {
            return response()->json([

                'success'=>false,

                'message'=>'No user found'

            ],404);
        }



        $user->update([

            'preferred_language'=>$request->language

        ]);



        return response()->json([

            'success'=>true,

            'message'=>'Language preference updated',

            'language'=>$request->language

        ]);

    }



    public function show()
    {

        $user = User::first();


        return response()->json([

            'success'=>true,

            'language'=>$user->preferred_language ?? 'en'

        ]);

    }

}