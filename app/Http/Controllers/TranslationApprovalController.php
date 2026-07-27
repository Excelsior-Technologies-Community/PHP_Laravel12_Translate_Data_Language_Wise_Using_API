<?php

namespace App\Http\Controllers;

use App\Models\PostTranslation;
use Illuminate\Http\Request;


class TranslationApprovalController extends Controller
{


    public function pending()
    {

        $translations = PostTranslation::where(
            'status',
            'pending'
        )
        ->with('post')
        ->get();



        return response()->json([

            'success'=>true,

            'data'=>$translations

        ]);

    }





    public function approve($id)
    {

        $translation = PostTranslation::find($id);



        if(!$translation)
        {
            return response()->json([

                'success'=>false,

                'message'=>'Translation not found'

            ],404);
        }



        $translation->update([

            'status'=>'approved'

        ]);



        return response()->json([

            'success'=>true,

            'message'=>'Translation approved successfully'

        ]);

    }






    public function reject($id)
    {

        $translation = PostTranslation::find($id);



        if(!$translation)
        {
            return response()->json([

                'success'=>false,

                'message'=>'Translation not found'

            ],404);
        }



        $translation->update([

            'status'=>'rejected'

        ]);



        return response()->json([

            'success'=>true,

            'message'=>'Translation rejected successfully'

        ]);

    }



}