<?php

namespace App\Http\Controllers\API\ZanMalipo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\ZanMalipo\ZanMalipoController;
use App\Jobs\SendBillToRabbitMQ;

class BillController extends Controller
{
    public static function create_bill($billDetails)
    {
        $payerName = $billDetails[0]->first_name." ".$billDetails[0]->last_name;
        date_default_timezone_set('Africa/Dar_es_Salaam');
        $billGenDt = date('Y-m-d').'T'.date('H:i:s');
        $billExprDt = date('Y-m-d', strtotime('+30 day')).'T'.date('H:i:s');
        $data_string = "";   
        $data_string.="<gepgBillSubReq> 
        <BillHdr>
        <SpCode>SP20000</SpCode> 
        <RtrRespFlg>true</RtrRespFlg> 
        </BillHdr> 
        <BillTrxInf> 
        <BillId>".$billDetails[0]->uuid."</BillId> 
        <SubSpCode>1001</SubSpCode> 
        <SpSysId>eGAZPHP001</SpSysId> 
        <BillAmt>".doubleVal($billDetails[0]->bill_amount)."</BillAmt> 
        <MiscAmt>0</MiscAmt> 
        <BillExprDt>".$billExprDt."</BillExprDt> 
        <PyrId>".$billDetails[0]->uuid."</PyrId> 
        <PyrName>".$payerName."</PyrName> 
        <BillDesc>ZANMALIPO</BillDesc> 
        <BillGenDt>".$billGenDt."</BillGenDt> 
        <BillGenBy>SystemGenerated</BillGenBy> 
        <BillApprBy>SystemGenerated</BillApprBy> 
        <PyrCellNum>".$billDetails[0]->phone_number."</PyrCellNum> 
        <PyrEmail>".$billDetails[0]->email."</PyrEmail> 
        <Ccy>".$billDetails[0]->cost_unit."</Ccy>";
        if($billDetails[0]->cost_unit == "USD"){
            $data_string.="<BillEqvAmt>".doubleVal($billDetails[0]->bill_amount * 2500)."</BillEqvAmt>";
        }
        else{
            $data_string.="<BillEqvAmt>".doubleVal($billDetails[0]->bill_amount)."</BillEqvAmt>";
        }
        $data_string.="<RemFlag>true</RemFlag> 
        <BillPayOpt>1</BillPayOpt> 
        <BillItems> 
        <BillItem> 
        <BillItemRef>".$billDetails[0]->service_name."</BillItemRef> 
        <UseItemRefOnPay>N</UseItemRefOnPay>";
        if($billDetails[0]->cost_unit == "USD"){
            $data_string.="<BillItemAmt>".doubleVal($billDetails[0]->bill_amount * 2500)."</BillItemAmt>";
        }
        else{
            $data_string.="<BillItemAmt>".doubleVal($billDetails[0]->bill_amount)."</BillItemAmt>";
        }
        if($billDetails[0]->cost_unit == "USD"){
            $data_string.="<BillItemEqvAmt>".doubleVal($billDetails[0]->bill_amount * 2500)."</BillItemEqvAmt>";
        }
        else{
            $data_string.="<BillItemEqvAmt>".doubleVal($billDetails[0]->bill_amount)."</BillItemEqvAmt>";
        }
        $data_string.="<BillItemMiscAmt>0</BillItemMiscAmt> 
        <GfsCode>".$billDetails[0]->gfs_code."</GfsCode> 
        </BillItem>
        </BillItems> 
        </BillTrxInf> 
        </gepgBillSubReq>";

        $privateKeyFilePath="Certificates/testprivate.pfx";
        $privateKeyPass='test2024';
        $privateKeyAlias='gepgclient';

        $signedString = ZanMalipoController::createSignature($data_string,$privateKeyPass,$privateKeyAlias, $privateKeyFilePath);
    
        $xmlPayload = "<Gepg>".$data_string."<gepgSignature>".$signedString."</gepgSignature></Gepg>";
        // SendBillToRabbitMQ::dispatch(array("data"=>$xmlPayload));
        $url="https://uat1.gepg.go.tz/api/bill/sigqrequest";
        $ackString = ZanMalipoController::sendRequest($xmlPayload,$url);
        return $ackString;
    }

    public static function reuse_bill($billDetails)
    {
        $payerName = $billDetails[0]->first_name." ".$billDetails[0]->last_name;
        date_default_timezone_set('Africa/Dar_es_Salaam');
        $billGenDt = date('Y-m-d').'T'.date('H:i:s');
        $billExprDt = date('Y-m-d', strtotime('+30 day')).'T'.date('H:i:s');
        $data_string = "";   
        $data_string.="<gepgBillSubReq> 
        <BillHdr>
        <SpCode>SP20000</SpCode> 
        <RtrRespFlg>true</RtrRespFlg> 
        </BillHdr> 
        <BillTrxInf> 
        <BillId>".$billDetails[0]->uuid."</BillId> 
        <SubSpCode>1001</SubSpCode> 
        <SpSysId>eGAZPHP001</SpSysId> 
        <BillAmt>".doubleVal($billDetails[0]->bill_amount)."</BillAmt> 
        <MiscAmt>0</MiscAmt> 
        <BillExprDt>".$billExprDt."</BillExprDt> 
        <PyrId>".$billDetails[0]->uuid."</PyrId> 
        <PyrName>".$payerName."</PyrName> 
        <BillDesc>ZANMALIPO</BillDesc> 
        <BillGenDt>".$billGenDt."</BillGenDt> 
        <BillGenBy>SystemGenerated</BillGenBy> 
        <BillApprBy>SystemGenerated</BillApprBy>
        <PyrCellNum>".$billDetails[0]->phone_number."</PyrCellNum> 
        <PyrEmail>".$billDetails[0]->email."</PyrEmail> 
        <Ccy>".$billDetails[0]->cost_unit."</Ccy>";
        if($billDetails[0]->cost_unit == "USD"){
            $data_string.="<BillEqvAmt>".doubleVal($billDetails[0]->bill_amount * 2500)."</BillEqvAmt>";
        }
        else{
            $data_string.="<BillEqvAmt>".doubleVal($billDetails[0]->bill_amount)."</BillEqvAmt>";
        }
        $data_string.="<RemFlag>true</RemFlag> 
        <BillPayOpt>1</BillPayOpt> 
        <PayCntrNum>".$billDetails[0]->control_number."</PayCntrNum>
        <BillItems> 
        <BillItem> 
        <BillItemRef>".$billDetails[0]->service_name."</BillItemRef> 
        <UseItemRefOnPay>N</UseItemRefOnPay>";
        if($billDetails[0]->cost_unit == "USD"){
            $data_string.="<BillItemAmt>".doubleVal($billDetails[0]->bill_amount * 2500)."</BillItemAmt>";
        }
        else{
            $data_string.="<BillItemAmt>".doubleVal($billDetails[0]->bill_amount)."</BillItemAmt>";
        }
        if($billDetails[0]->cost_unit == "USD"){
            $data_string.="<BillItemEqvAmt>".doubleVal($billDetails[0]->bill_amount * 2500)."</BillItemEqvAmt>";
        }
        else{
            $data_string.="<BillItemEqvAmt>".doubleVal($billDetails[0]->bill_amount)."</BillItemEqvAmt>";
        }
        $data_string.="<BillItemMiscAmt>0</BillItemMiscAmt> 
        <GfsCode>".$billDetails[0]->gfs_code."</GfsCode> 
        </BillItem>
        </BillItems> 
        </BillTrxInf> 
        </gepgBillSubReq>";

        $privateKeyFilePath="Certificates/testprivate.pfx";
        $privateKeyPass='test2024';
        $privateKeyAlias='gepgclient';

        $signedString = ZanMalipoController::createSignature($data_string,$privateKeyPass,$privateKeyAlias, $privateKeyFilePath);
    
        $xmlPayload = "<Gepg>".$data_string."<gepgSignature>".$signedString."</gepgSignature></Gepg>";
        // SendBillToRabbitMQ::dispatch(array("data"=>$xmlPayload));
        $url="https://uat1.gepg.go.tz/api/bill/sigqrequest_reuse";
        $ackString = ZanMalipoController::sendRequest($xmlPayload,$url);
        return $ackString;
    }

    public static function update_bill($billDetails)
    {
        $payerName = $billDetails[0]->first_name." ".$billDetails[0]->last_name;
        date_default_timezone_set('Africa/Dar_es_Salaam');
        $billGenDt = date('Y-m-d').'T'.date('H:i:s');
        $billExprDt = date('Y-m-d', strtotime('+30 day')).'T'.date('H:i:s');
        $data_string = "";   
        $data_string.="<gepgBillSubReq>
        <BillHdr>
        <SpCode>SP023</SpCode>
        <RtrRespFlg>true</RtrRespFlg>
        </BillHdr>
        <BillTrxInf>
        <BillId>".$billDetails[0]->uuid."</BillId> 
        <SpSysId>eGAZPHP001</SpSysId> ";// utabadilisha ukipewa SpSysId na zanMalipo
        $data_string.="<BillExprDt>".$billExprDt."</BillExprDt> 
        <BillRsv1></BillRsv1>
        <BillRsv2></BillRsv2>
        <BillRsv3></BillRsv3>
        </BillTrxInf>
        </gepgBillSubReq>";

        $privateKeyFilePath="Certificates/testprivate.pfx";
        $privateKeyPass='test2024';
        $privateKeyAlias='gepgclient';

        $signedString = ZanMalipoController::createSignature($data_string,$privateKeyPass,$privateKeyAlias, $privateKeyFilePath);
    
        $xmlPayload = "<Gepg>".$data_string."<gepgSignature>".$signedString."</gepgSignature></Gepg>";
        // SendBillToRabbitMQ::dispatch(array("data"=>$xmlPayload));
        $url="https://uat1.gepg.go.tz/api/bill/sigqrequest_change";
        $ackString = ZanMalipoController::sendRequest($xmlPayload,$url);
        return $ackString;
    }

    public static function cancel_bill($billDetails,$reason)
    {
        $payerName = $billDetails[0]->first_name." ".$billDetails[0]->last_name;
        date_default_timezone_set('Africa/Dar_es_Salaam');
        $billGenDt = date('Y-m-d').'T'.date('H:i:s');
        $billExprDt = date('Y-m-d', strtotime('+30 day')).'T'.date('H:i:s');
        $data_string = "";   
        $data_string.="<gepgBillCanclReq>
        <SpCode>SP108</SpCode>
        <SpSysId>tjv47</SpSysId>";// utabadilisha ukipewa spsys na zanMalipo
        $data_string.="<CanclReasn>".$reason."<CanclReasn>
        <BillId>".$billDetails[0]->uuid."</BillId>
        </gepgBillCanclReq>";

        $privateKeyFilePath="Certificates/testprivate.pfx";
        $privateKeyPass='test2024';
        $privateKeyAlias='gepgclient';

        $signedString = ZanMalipoController::createSignature($data_string,$privateKeyPass,$privateKeyAlias, $privateKeyFilePath);
    
        $xmlPayload = "<Gepg>".$data_string."<gepgSignature>".$signedString."</gepgSignature></Gepg>";
        // SendBillToRabbitMQ::dispatch(array("data"=>$xmlPayload));
        $url="https://uat1.gepg.go.tz/api/bill/sigcancel_request";
        $ackString = ZanMalipoController::sendRequest($xmlPayload,$url);
        return $ackString;
    }

    public static function reconsile_bill($reconsDetails)
    {
        $data_string = "";   
        $data_string.="<gepgSpReconcReq>
        <SpReconcReqId>".$reconsDetails->uuid."</SpReconcReqId>
        <SpCode>SP108</SpCode>
        <SpSysId>INST001</SpSysId>
        <TnxDt>".$reconsDetails->recon_date."</TnxDt>
        <ReconcOpt>1</ReconcOpt>
        </gepgSpReconcReq>";

        $privateKeyFilePath="Certificates/testprivate.pfx";
        $privateKeyPass='test2024';
        $privateKeyAlias='gepgclient';

        $signedString = ZanMalipoController::createSignature($data_string,$privateKeyPass,$privateKeyAlias, $privateKeyFilePath);
    
        $xmlPayload = "<Gepg>".$data_string."<gepgSignature>".$signedString."</gepgSignature></Gepg>";
        // SendBillToRabbitMQ::dispatch(array("data"=>$xmlPayload));
        $url="https://uat1.gepg.go.tz/api/reconciliations/sig_sp_qrequest";
        $ackString = ZanMalipoController::sendRequest($xmlPayload,$url);
        return $ackString;
    }
}
