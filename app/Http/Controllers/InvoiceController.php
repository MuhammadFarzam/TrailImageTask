<?php


namespace App\Http\Controllers;

use App\Invoice;
use Illuminate\Http\Request;


class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Invoice Found',
            'data' => $invoices
        ]);
    }

    public function show(Request $request, $identifier)
    {
        $response = [];

        $response['status'] = 'success';
        $response['message'] = 'Invoice Found';

        $invoices = Invoice::find($identifier);
        if(!$invoices){
            $invoices = Invoice::where('invoice_no',$identifier)->get();
        
            if($invoices->isEmpty()){
                $response['message'] = 'Invoice not Found';
                $response['status'] = 'error';
            }
        }
        $response['data'] = $invoices;
        return response()->json($response);
    }

}