<?php

namespace App\Http\Controllers;

use App\Invoice;
use Faker\Provider\Image;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;

class MultipleImageUploadCOntroller extends Controller
{
    
    public $ocrObj;

    public function __construct()
    {
        $this->ocrObj = new TesseractOCR();   
    }

    /**
     * Function To handle Upload Images 
     */
    public function uploadMultipleImages(Request $request){


        $request->validate([
            'username' => 'required',
            'images' => 'required',
            'images.*' => 'mimes:png,jpeg,jpg,pdf'
        ]);

        $responseReturn = [
            'error' => [],
            'success' => []
        ]; 
        $dataForInsert = [];
        $files = $request->file('images');
        $username = $request->get('username');
        
        if($request->hasFile('images'))
        {
            foreach ($files as $file) {
                $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $imageName = $fileName.'.'.$file->extension();  
                $file->move(public_path('images'), $imageName);

                $parseObj = $this->getStringFromLocalImages($imageName);
                $parseObj['invoice']['created_by'] = $username;
                
                if(!$parseObj['status']){
                    $responseReturn['error'][] = [
                        'image_name' => $imageName,
                        'message' => $parseObj['message']
                    ]; 
                }else{
                    $dataForInsert[] = $parseObj['invoice'];
                    $responseReturn['success'][] = [
                        'image_name' => $imageName,
                        'message' => $parseObj['message']
                    ]; 
                }
            }
        }

        if(!empty($dataForInsert)){
            Invoice::insert($dataForInsert);
        }
        return response()->json($responseReturn);

    }


    /**
     * Function for getting String Through Package Method
     */
    public function getStringFromLocalImages($image){
        $this->ocrObj->image(public_path('images').'/'.$image);
        $data = $this->ocrObj->run();
        $data = preg_replace('/\s*/m','',$data);
        return $this->setFieldsForSpecificInvoice($data);
    }


    /**
     * Function For Parsing Specific Invoice
     */
    public function setFieldsForSpecificInvoice($data){

        $invoice = [];
        //Condition to Handle Blur Invoice
        if(empty($data)){
            return ['status' => false,'message'=>'Failed Image Detection! Please upload once again','invoice' => $invoice];
        }
        $invoice['user_name'] = $invoice['invoice_no'] = '';
        if(strpos($data, 'daraz') > 0 || strpos($data, 'daraz.pk') > 0){
            //For Daraz
            $info = $this->getStringBetween($data,'SALESINVOICE','InvoiceDate');
            if(!empty($info)){
                $billing = explode(':',$info);
                if(!empty($billing)){
                    $invoice['user_name'] = preg_replace('/[^a-zA-Z]/', '', $billing[1]);
                    $invoice['invoice_no'] = filter_var($billing[1], FILTER_SANITIZE_NUMBER_INT);
                }
                
                $info = $this->getStringBetween($data,'TOTAL:','Totalchargesfor');
                $invoice['subtotal'] = $this->getStringBetween($info,'TotalUnitPrice','TotalDiscount') ? $this->getStringBetween($info,'TotalUnitPrice','TotalDiscount') : '0';
                $invoice['discount'] = $this->getStringBetween($info,'TotalDiscount','TotalShipping') ? $this->getStringBetween($info,'TotalDiscount','TotalShipping') : '0';
                $invoice['shipping'] = $this->getStringBetween($info,'TotalShipping','TotalRs') ? $this->getStringBetween($info,'TotalShipping','TotalRs') : '0';
                $invoice['tax'] = '0';
                $invoice['total_ammount'] = $this->getStringBetween($info,'Rs','TotalUnitPrice') ? 'Rs'.$this->getStringBetween($info,'Rs','TotalUnitPrice') : '0';
            }
        }else{
            //For General Invoice
            $invoice['user_name'] = substr($data, strpos($data, 'Customer')+8,10);
            $invoice['invoice_no'] = $this->getStringBetween($data,'InvoiceNo.:','InvoiceDate') ? $this->getStringBetween($data,'InvoiceNo.:','InvoiceDate') : '';

            $info = substr($data, strpos($data, 'InvoiceSummary'));
            $invoice['subtotal'] = $this->getStringBetween($info,'Subtotal','tax');
            $invoice['discount'] = $this->getStringBetween($info,'TotalDiscount','TotalShipping') ? $this->getStringBetween($info,'TotalDiscount','TotalShipping') : '0';
            $invoice['shipping'] = $this->getStringBetween($info,'TotalShipping','TotalRs') ? $this->getStringBetween($info,'TotalShipping','TotalRs') : '0';
            $invoice['tax'] = $this->getStringBetween($info,'tax-1(2%)','Total');
            $invoice['total_ammount'] = substr($data, strpos($data, 'Total')+5);

        }

        //Condition to Unsupported Invoices
        if($invoice['invoice_no'] == '' || $invoice['user_name'] == ''){
            return ['status' => false,'message' => 'Invoice not Supported','invoice' => $invoice];
        }
        return ['status' => true,'message' => 'successfully upload','invoice' => $invoice];
    }



    /**
     * Getting String Between Two Words
     */
    function getStringBetween($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}
