<?php

namespace App\Http\Controllers;


use App\Models\PostTranslation;
use Illuminate\Support\Facades\DB;


class TranslationAnalyticsController extends Controller
{


    public function index()
    {


        $total =
        PostTranslation::count();



        $languages =
        PostTranslation::select(

            'locale',

            DB::raw('count(*) as total')

        )
        ->groupBy('locale')
        ->get();



        $mostUsed =
        PostTranslation::select(

            'locale',

            DB::raw('count(*) as total')

        )
        ->groupBy('locale')
        ->orderByDesc('total')
        ->first();



        return response()->json([

            'success'=>true,


            'data'=>[

                'total_translations'=>$total,


                'languages'=>$languages,


                'most_used_language'=>

                $mostUsed 
                ? $mostUsed->locale
                : null

            ]

        ]);

    }

}