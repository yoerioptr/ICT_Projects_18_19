<?php

namespace App\Http\Controllers;

use App\Mail\PaymentStatus;
use App\Payment;
use App\PaymentsPerTraveller;
use App\Traveller;
use App\TravellersPerTrip;
use App\Trip;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PaymentsOverviewController extends Controller
{
    protected $aSearchValues = array(
        'last_name' => 'Familienaam',
        'first_name' => 'Voornaam',
        'iban' => 'Bankrekening',
        'amount' => 'Betaling',
        'travellers.traveller_id' => 'Reiziger Id',
        'price' => 'Prijs'
    );
    /**
     * Shows table with userdata(traveller, study, payment)
     * @author Nico Schelfhout
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showTable($iTripId = null){
        if(!Auth::user()->isOrganizer()){
            return redirect('info');
        }
        $aActiveTrips = Trip::where('is_active', true)->get();
        $oUser = Auth::user();
        /* Get all trips that can be accessed by the user */
        if ($oUser->role == 'admin') {
            $aOrganizerTrips = $aActiveTrips;
        } else if ($oUser->role == 'guide') {
            $aOrganizerTrips = User::where('users.user_id', $oUser->user_id)->where('is_active', true)->where('is_organizer', true)
                ->join('travellers', 'travellers.user_id', '=', 'users.user_id')
                ->join('travellers_per_trips', 'travellers_per_trips.traveller_id', '=', 'travellers.traveller_id')
                ->join('trips', 'trips.trip_id', '=', 'travellers_per_trips.trip_id')
                ->get();
        }

        /* Check if user can access the trip */
        if ($iTripId != null) {
            $bCanAccess = false;
            foreach ($aOrganizerTrips as $oTrip) {
                if ($iTripId == $oTrip->trip_id) {
                    $bCanAccess = true;
                }
            }
            if ($bCanAccess == false) {
                return 'U heeft geen rechten om deze lijst te bekijken';
            }
        } else {
            $iTripId = $aOrganizerTrips[0]->trip_id;
            $bCanAccess = true;
        }
        $aActiveTrips = array();
        foreach (Trip::where('is_active', true)->get() as $oTrip) {
            array_push($aActiveTrips, array(
                'oTrip' => $oTrip,
                'iCount' => TravellersPerTrip::where('trip_id', $oTrip->trip_id)
                    ->get()
                    ->count(),
            ));
        }
        foreach ($aOrganizerTrips as $oTrip) {
            $aAuthenticatedTrips[$oTrip->trip_id] = $oTrip->trip_id;
        }
        $aSearchValues = $this->aSearchValues;
        $oCurrentTrip = Trip::where('trip_id', $iTripId)->first();
        $userdata = Traveller::getTravellersWithPayment($iTripId);



        return view('user.payment.pay_overview',['userdata' => $userdata,
            'oCurrentTrip' => $oCurrentTrip,
            'aActiveTrips' => $aActiveTrips,'aAuthenticatedTripId' => $aAuthenticatedTrips]);
    }
    public static function showPayment(Request $request){
//        die();
        $paymentdata = PaymentsPerTraveller::getPaymentsPerTravellersOverview($request->post('traveller_id'));
        return response()->json(['paymentdata'=>$paymentdata]);
    }

    public static function deletePayment(Request $request){
        PaymentsPerTraveller::where('paymentPerTravellers_id', $request->post('paymentPerTravellers_id'))->delete();
        Payment::where('traveller_id', $request->post('traveller_id'))
            ->update([
                'payment_date' => $request->post('payment_date'),
                'amount' => DB::raw('amount-'.$request->post('amount'))

            ]);
    }
    /**
     * @author Nico Schelfhout
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addPayment(Request $request)
    {
//        return $request->post();
        //Get the input
        $input = $request->all();

        //Get the validation rules
        $rules = [
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required',
        ];

        //Get the messages
        $messages = $this->messages();

        //Validation
        $validator = Validator::make($input,$rules,$messages );

        //If the validation fails, return back to the view with the errors and the input you've given
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $amount=$request->post('amount');
        //Insert new record into table
        Payment::where('traveller_id', $request->post('traveller_id'))
        ->update([
            'payment_date' => $request->post('payment_date'),
            'amount' => DB::raw('amount+'.$request->post('amount'))

        ]);
        PaymentsPerTraveller::insert([
            'traveller_id' => $request->post('traveller_id'),
            'amount' => $request->post('amount'),
            'payment_date' => $request->post('payment_date'),
            'paymentPerTravellers_id' => $request->post('payment_id')
        ]);
        //return back to the view with the succes message
        return back();
    }


    /**Author: Nico Schelfhout
     * @return array
     *
     * Returns an array with custom error messages
     */
    private function messages(){
        return [
            'amount.required' => 'Gelieve een betaling toe te voegen',
            'amount.numeric' => 'Een betaling kan enkel getallen bevatten',
            'amount.min' => 'U kan niet minder dan 0 betalen',
            'amount.max' => 'U kan niet meer betalen dan de prijs van de reis',
            'payment_date.required'=>'Gelieve een datum toe te voegen'


        ];
    }

    public function sendMail(Request $request){
        $bsendMail = $request->post("sendMail");
        $aTripIdGuide = TravellersPerTrip::select('trip_id')
            ->join('travellers', 'travellers_per_trips.traveller_id', '=', 'travellers.traveller_id')
            ->join('users', 'travellers.user_id', '=', 'users.user_id')
            ->where('username', Auth::user()->username)
            ->get();
        $aName = Traveller::select('last_name', 'first_name')->where('user_id',Auth::id())->first();
        $sName = $aName['first_name']." ".$aName['last_name'];
        foreach ($aTripIdGuide as $iTrip){
            $this->sendMailToStudentsInTrip($iTrip["trip_id"],$sName);
        }

        return response()->json(["mailsSent"=>true]);
    }

    public function sendMailToStudentsInTrip($sTripId,$sBegeleider=" "){

        $oStudents = Traveller::join('travellers_per_trips', 'travellers_per_trips.traveller_id', '=', 'travellers.traveller_id')->where('trip_id',$sTripId)->get();
        $sTrip = Trip::where('trip_id',$sTripId)->first();
        $sTripNaam = $sTrip->name;
        $iPrijs=$sTrip->price;
        foreach ($oStudents as $oStudent){
            $aBetalingen = Payment::where('traveller_id',$oStudent->traveller_id)->get()->pluck('amount');
            $iBetaald = 0;
            foreach ($aBetalingen as $iAmount){
                $iBetaald+=$iAmount;
            }
            $this->sendMailTo($oStudent->email,$oStudent->first_name,$iBetaald, $iPrijs-$iBetaald,$sTripNaam,$sBegeleider);
        }
    }

    public function sendMailTo($email, $studentNaam,$betaald,$teBetalen,$reisNaam,$begeleider) {
        $aMailData = [
            'studentNaam' => $studentNaam,
            'email' => $email,
            'betaald' => $betaald,
            'teBetalen'=>$teBetalen,
            'reisNaam'=>$reisNaam,
            'begeleider'=>$begeleider
        ];
        Mail::to($email)->send(new PaymentStatus($aMailData));

    }

}
