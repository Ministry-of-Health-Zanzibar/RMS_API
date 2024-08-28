<?php

namespace App\Http\Controllers\API\ZanMalipo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\ZanMalipo\ZanMalipoController;
use App\Http\Controllers\API\Setup\SendMsgController;
use App\Models\Bills;
use App\Enums\Status;
use DB;

class BillCallBackController extends Controller
{
    private  $request;
    private $signatureObject;
    private $token;
    
    public function __construct()
    {
        date_default_timezone_set('Africa/Dar_es_Salaam');
        // $this->request =file_get_contents('php://input');
           
    }

    public function receive_controll_no(Request $request)
    {
        $xmlContent = $request->getContent();
        $time = date('Y-m-d H:i:s');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $privateKeyPath='Certificates/testprivate.pfx';
        error_log("\n".'['.$time.']'." request from Gepg ".$this->request, 3, storage_path('/logs/bill.log'));
            $values = $this->getXMLData($xmlContent);
          
            $statuscodes = $values['TrxStsCode'];
            $TrxSts = $values['TrxSts'];
            $billid = $values['billid'];
            $codes = explode(';',$statuscodes);
            $control_no = $values['PayCntrNum'];
            $signature=$values['gepgSignature'];
            $content='<gepgBillSubResp>'.$values['gepgBillSubResp'].'</gepgBillSubResp>';
            $bill_status = Status::awaiting_fees->value;
            
            $update_bills = DB::table('bills')
                                ->where('uuid', $billid)
                                ->update([
                                    'control_number' => $control_no,
                                    'bill_status' => $bill_status,
                                    'bill_response_code' => '7279'
                                ]);

            if($TrxSts == "GS"){
                $status_change = $this->change_app_status($billid,$bill_status);
            }
           
            $content ='<gepgBillSubRespAck>
                        <TrxStsCode>7101</TrxStsCode>      
                       </gepgBillSubRespAck>';  
                        $generatedSignature = ZanMalipoController::createSignature($content, 'test2024', 'gepglient', $privateKeyPath);
                        $response = "<Gepg>".$content."<gepgSignature>".$generatedSignature."</gepgSignature></Gepg>";

            return response($response, 200)->header('Content-type', 'application/xml');
    }

    public function receive_payment(Request $request)
    {
        $xmlContent = $request->getContent();
        $time = date('Y-m-d H:i:s');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $privateKeyPath='Certificates/testprivate.pfx';
        error_log("\n".'['.$time.']'." request from Gepg ".$this->request, 3, storage_path('/logs/bill.log'));
            $values = $this->getPaymentXMLData($xmlContent);

            $receipt = $values['PspReceiptNumber'];
            $paymentDate = $values['TrxDtTm'];
            $controlno = $values['PayCtrNum'];
            $billid = $values['BillId'];
            $payRef = $values['PayRefId'];
            $PspName = $values['PspName'];
            $signature = $values['gepgSignature'];
            $content = '<gepgPmtSpInfo>'.$values['gepgPmtSpInfo'].'</gepgPmtSpInfo>';

            $bill_status = Status::paid->value;
            
            $update_bills = DB::table('bills')
                                ->where('uuid', $billid)
                                ->update([
                                    'paid_date' => $paymentDate,
                                    'bill_status' => $bill_status,
                                    'receipt_number' => $receipt,
                                    'reference_number' => $payRef,
                                    'psp_name' => $PspName,
                                ]);

           $status_change = $this->change_app_status($billid,$bill_status);

            $content ='<gepgPmtSpInfoAck>
                        <TrxStsCode>7101</TrxStsCode>
                        </gepgPmtSpInfoAck>';  
                        $generatedSignature = ZanMalipoController::createSignature($content, 'test2024', 'gepglient', $privateKeyPath);
                        $response = "<Gepg>".$content."<gepgSignature>".$generatedSignature."</gepgSignature></Gepg>";

            return response($response, 200)->header('Content-type', 'application/xml');
    }

    public function receive_reconciliation(Request $request)
    {
        $xmlContent = $request->getContent();
        $time = date('Y-m-d H:i:s');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $privateKeyPath='Certificates/testprivate.pfx';
        $xmlObject = simplexml_load_string($xmlContent);

        $jsonData = json_encode($xmlObject, JSON_PRETTY_PRINT);
        $ArrayData = json_decode($jsonData, true);
        $jsonData = json_encode($ArrayData["gepgSpReconcResp"]["ReconcTrans"], JSON_PRETTY_PRINT);
        $ArrayData = json_decode($jsonData)->ReconcTrxInf;

        $bill_status = Status::paid->value;

        if(sizeof($ArrayData) > 0)
        {
            foreach ($ArrayData as $data) 
            {
                $update_bills = DB::table('bills')
                                    ->where('uuid', $data->SpBillId)
                                    ->update([
                                        'paid_date' => $data->TrxDtTm,
                                        'bill_status' => $bill_status,
                                        'receipt_number' => $data->pspTrxId,
                                        'reference_number' => $data->PayRefId,
                                        'psp_name' => $data->PspName,
                                    ]);
            }
        }

        $privateKeyPath='Certificates/testprivate.pfx';
               
        $content = '<gepgSpReconcRespAck>
        <ReconcStsCode>7101</ReconcStsCode>
            </gepgSpReconcRespAck>';  
                $generatedSignature = ZanMalipoController::createSignature($content, 'test2024', 'gep', $privateKeyPath);
            $response ="<Gepg>".$content."<gepgSignature>".$generatedSignature."</gepgSignature></Gepg>";

            return response($response, 200)->header('Content-type', 'application/xml'); 
    }

    function getXMLData($request)
    {
        $values = array();
        $values['billid'] = $this->get_string_between($request, '<BillId>', '</BillId>');
        $values['PayCntrNum'] = $this->get_string_between($request, '<PayCntrNum>', '</PayCntrNum>');
        $values['TrxSts'] = $this->get_string_between($request, '<TrxSts>', '</TrxSts>');
        $values['TrxStsCode'] =$this->get_string_between($request, '<TrxStsCode>', '</TrxStsCode>');
        $values['gepgSignature']=$this->get_string_between($request, '<gepgSignature>', '</gepgSignature>');
        $values['gepgBillSubResp']=$this->get_string_between($request, '<gepgBillSubResp>', '</gepgBillSubResp>');
        return $values;
    }

    function getPaymentXMLData($request)
    {
        $values = array();
        $values['BillId'] = $this->get_string_between($request, '<BillId>', '</BillId>');
        $values['PayCtrNum'] = $this->get_string_between($request, '<PayCtrNum>', '</PayCtrNum>');
        $values['TrxId'] = $this->get_string_between($request, '<TrxId>', '</TrxId>');
        $values['PspReceiptNumber'] =$this->get_string_between($request, '<PspReceiptNumber>', '</PspReceiptNumber>');
        $values['PayRefId']=$this->get_string_between($request, '<PayRefId>', '</PayRefId>');
        $values['PspName']=$this->get_string_between($request, '<PspName>', '</PspName>');
        $values['gepgSignature']=$this->get_string_between($request, '<gepgSignature>', '</gepgSignature>');
        $values['gepgPmtSpInfo']=$this->get_string_between($request, '<gepgPmtSpInfo>', '</gepgPmtSpInfo>');
        $values['TrxDtTm'] = $this->get_string_between($request, '<TrxDtTm>', '</TrxDtTm>');
        return $values;
    }

    // get data string from xml
    public function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public function change_app_status($billid,$bill_status)
    {
        if($bill_status == "AWAITING_FEES")
        {
            $bill_services = DB::table('bills')
                            ->join('bill_services', 'bill_services.bill_id', '=', 'bills.bill_id')
                            ->select('bill_services.service_id')
                            ->where('bills.uuid',$billid)
                            ->get();

            $service_id = $bill_services[0]->service_id;
            
            if($service_id == '10000001')
            {
                $billDetails = DB::table('bills')
                            ->join('pre_registrations', 'pre_registrations.bill_id', '=', 'bills.bill_id')
                            ->select('bills.bill_id','bills.first_name','bills.last_name','bills.phone_number','bills.bill_amount','bills.cost_unit','bills.control_number','pre_registrations.council_sender_id')
                            ->where('bills.uuid',$billid)
                            ->get();
            }
            else
            {
                $billDetails = DB::table('bills')
                            ->join('applicant_bills', 'applicant_bills.bill_id', '=', 'bills.bill_id')
                            ->select('bills.bill_id','bills.first_name','bills.last_name','bills.phone_number','bills.bill_amount','bills.cost_unit','bills.control_number','applicant_bills.council_sender_id')
                            ->where('bills.uuid',$billid)
                            ->get();
            }

            $number = $billDetails[0]->phone_number;
            $first_name = $billDetails[0]->first_name;
            $last_name = $billDetails[0]->last_name;
            $senderId = $billDetails[0]->council_sender_id;
            $bill_amount = $billDetails[0]->bill_amount;
            $control_number = $billDetails[0]->control_number;
            $cost_unit = $billDetails[0]->cost_unit;

            //======= send SMS to client for about registration =======

            // $message = "Ndugu ".$first_name." ". $last_name ." unatakiwa kulipia kiasi  ". number_format($bill_amount) ." ".$cost_unit." kwa controll namba ". $control_number ." Ahsante.";

            // $respone_otp = new SendMsgController();
            // $respone_otp->sendOTP($number,$message,$senderId);

        }
        else
        {
            $bill_services = DB::table('bills')
                                ->join('bill_services', 'bill_services.bill_id', '=', 'bills.bill_id')
                                ->select('bill_services.service_id')
                                ->where('bills.uuid',$billid)
                                ->get();

            $service_id = $bill_services[0]->service_id;
            
            if($service_id == '10000001')
            {
                
            }
            else
            {
                $billDetails = DB::table('bills')
                                ->join('applicant_bills', 'applicant_bills.bill_id', '=', 'bills.bill_id')
                                ->select('bills.bill_id','bills.first_name','bills.last_name','bills.phone_number','bills.control_number','applicant_bills.app_registration_type_id')
                                ->where('bills.uuid',$billid)
                                ->get();

                $app_registration_type_id = $billDetails[0]->app_registration_type_id;

                $applicantRegistrationTypes = ApplicantRegistrationTypes::find($app_registration_type_id);
                $applicantRegistrationTypes->app_status  = $bill_status;
                $applicantRegistrationTypes->update();
            }
        }
        return true;
    }

}
