<?php

namespace App\Http\Controllers;

use App\Traveller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class UserDataController extends Controller
{
    public function showUsersAsMentor(Request $request)
    {
        $id = Auth::id();

        $aUserData = [];

        if ($request->post('button-filter')) {
            $aFilterChecked = [
                'email' => $request->post('email'),
                'phone' => $request->post('phone'),
            ];

            $sSelectString = 'lastname,firstname';

            foreach ($aFilterChecked as $sFilter => $bValue) {
                if ($bValue) {
                    $sSelectString .= ',' . $sFilter;
                }
            }

            $aUserData = Traveller::select(DB::raw($sSelectString))->paginate(2);
        } else {
            $aUserData = Traveller::paginate(2);
        }


        return view('user.filter.filter', [
            'aUserData' => $aUserData,
        ]);
    }

    /**
     * downloadExcel : this will download an excel file based on the session data of filters (the checked fields)
     */
    public function downloadExcel(Request $request)
    {
        $aUserFields = $request->session()->get('filters');
        $data = UserData::get($aUserFields)->toArray();

        return Excel::create('Gebruikers', function($excel) use ($data) {
            $excel->sheet('mySheet', function($sheet) use ($data)
            {
                $sheet->fromArray($data);
            });
        })->download('xlsx');
    }
}
