<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use JWTFactory;
use JWTAuth;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Validator;
use Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class PropertyController extends Controller
{

    public function getproperties(Request $request) {
        try {
            $points = json_decode($request->get('polygon'));
            $polygonStr = '';
            if ($points) {
                for ($i = 0; $i < count($points); $i++) {
                    if ($i == 0)
                        $polygonStr = 'POLYGON(('.$polygonStr.strval($points[$i][0])." ".strval($points[$i][1]);
                    else
                        $polygonStr = $polygonStr.", ".strval($points[$i][0])." ".strval($points[$i][1]);
                }
                $polygonStr = $polygonStr.'))';
                $properties = DB::select("SELECT * FROM `residential` 
                    WHERE CONTAINS(GEOMFROMTEXT(?), POINT(latitude, longitude)) LIMIT 150", [$polygonStr]);
                return response()->json(['success'=>true, 'data'=>$properties], 201);
            } else {
                $properties = DB::table('residential')->take(150)->get();
                return response()->json(['success'=>true, 'data'=>$properties], 201);
            }

        } catch(\Exception $e) {
            return response()->json(['error'=>$e], 500);
        }
    }
}
