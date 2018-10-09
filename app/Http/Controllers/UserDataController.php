<?php

namespace App\Http\Controllers;

use App\Traveller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class UserDataController extends Controller
{
    protected $aFilterList = [
        'email' => 'Email',
        'country' => 'Land',
        'address' => 'Adres',
        'gender' => 'Geslacht',
        'phone' => 'Telefoon',
        'emergency_phone_1' => 'Nood Contact 1',
        'emergency_phone_2' => 'Nood Contact 2',
        'nationality' => 'Nationaliteit',
        'birthdate' => 'Geboortedatum',
        'medical_info' => 'Medische Info',
    ];
    private $request;

    public function showUsersAsMentor(Request $request)
    {
        $this->request = $request;
        $id = Auth::id();

        $aFiltersChecked = $this->getCheckedFilters();

        if ($request->post('button-filter')) {
            $aUserData = Traveller::select(array_keys($aFiltersChecked))->paginate(2);
        }
        else {
            $aUserData = Traveller::select(array_keys($aFiltersChecked))->paginate(2);;
        }

        if ($request->post('export') == 'exel') {
            $this->downloadExcel();
        }
        if ($request->post('export') == 'pdf') {
            $this->downloadPDF();
        }

        return view('user.filter.filter', [
            'aUserData' => $aUserData,
            'aFilterList' => $this->aFilterList,
            'aFiltersChecked' => $aFiltersChecked
        ]);
    }

    /**
     * downloadExcel : this will download an excel file based on the session data of filters (the checked fields)
     */
    private function downloadExcel() {
        $aUserFields = $this->getCheckedFilters();

        $data = Traveller::select(array_keys($aUserFields))->get()->toArray();

        return Excel::create('Gebruikers', function($excel) use ($data) {
            $excel->sheet('mySheet', function($sheet) use ($data)
            {
                $sheet->fromArray($data);
            });
        })->download('xlsx');
    }

    /**
     * downloadPDF: deze functie zorgt ervoor dat je een pdf van de gefilterde lijst download.
     */
    private function downloadPDF(){
        $aUserFields = $this->getCheckedFilters();

        $data = Traveller::select(array_keys($aUserFields))->get()->toArray();

        try {
            $spreadsheet = new Spreadsheet();  /*----Spreadsheet object-----*/
            $spreadsheet->getActiveSheet();
            $activeSheet = $spreadsheet->getActiveSheet();
            $activeSheet->fromArray($aUserFields,NULL, 'A1');
            $activeSheet->fromArray($data,NULL,'A2');

            IOFactory::registerWriter("PDF", Dompdf::class);
            $writer = IOFactory::createWriter($spreadsheet, 'PDF');

            header('Content-Disposition: attachment; filename="gefilterte_tabel.pdf"');
            //$activeSheet->setCellValue('A1' , 'New file content')->getStyle('A1')->getFont()->setBold(true);

            $writer->save("php://output");
        } catch (Exception $e) {
        }

    }

    /**
     * Returns array of fields based on the current selected filters
     *
     * @author Yoeri op't Roodt
     * @return array
     */
    private function getCheckedFilters() {
        $aFiltersChecked = array(
            'last_name' => 'Familienaam',
            'first_name' => 'Voornaam'
        );

        foreach ($this->aFilterList as $sFilterName => $sFilterText) {
            if ($this->request->post($sFilterName) != false) {
                $aFiltersChecked[$sFilterName] = $sFilterText;
            }
        }

        return $aFiltersChecked;
    }
}
