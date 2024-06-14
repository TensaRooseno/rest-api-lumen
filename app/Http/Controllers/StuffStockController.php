<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\Stuff;
use App\models\StuffStock;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiFormatter;

class StuffStockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function index(){
        try {
            $data = Stuff::with('stuffStock')->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request){
        // $validator = Validator::make
        // ($request->all(), [
        //     'stuff_id' => 'required',
        //     'total_available' => 'required',
        //     'total_defect' => 'required'
        // ]);

        // if($validator->fails()){
        //     return response()->json([
        //       'success' => false,
        //       'message' => 'Semua kolom wajib disi!',
        //         'data' => $validator->errors()
        //     ],400);
        // } else{
        //     $stock = StuffStock::updateOrCreate([
        //         'stuff_id' => $request->input('stuff_id')
        //     ],[
        //         'total_available' => $request->input('total_available'),
        //         'total_defect' => $request->input('total_defect')
        //     ]);


        //     if($stock) {
        //         return response()->json([
        //          'success' => true,
        //          'message' => 'Barang berhasil ditambahkan',
        //             'data' => $stock
        //         ],200);
        //     } else{
        //         return response()->json([
        //         'success' => false,
        //         'message' => 'Barang gagal ditambahkan',
        //         ],400);
        //     }
        // }

        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'total_available' => 'required',
                'total_defec' => 'required',
            ]);
            $stuffstock = StuffStock::create([
                'stuff_id' => $request->input('stuff_id'),
                'total_avaiable' => $request->input('total_available'),
                'total_defec' => $request->input('total_defec'),
            ]);
            return ApiFormatter::sendResponse(201, true, 'Barang berhasil disimpan!', $stuffstock);
        } catch (\throwable $th) {
            if ($th->validator->errors()) {
                return ApiFormatter::sendResponse(400, false, 'Terdapat kesalahan input, Silahkan coba lagi!', $th->validator->errors());
            } else {
                return ApiFormatter::sendResponse(400, false, 'Terdapat kesalahan input, Silahkan coba lagi!', $th->getMessage());
            }
        }
    }


    public function show($id){
        try {
            $stuffstock = StuffStock::with('stuff')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, "lihat barang dengan id $id, $stuffstock");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "data dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id) {
        try {
            $stuffstock = StuffStock::findORFail($id);
            $stuff_id = ($request->stuff_id) ? $request->stuff_id : $stuffstock->stuff_id;
            $total_available = ($request->total_available) ? $request->total_available : $stuffstock->total_available;
            $total_defec = ($request->total_defec) ? $request->total_defec : $stuffstock->total_defec;

            $stuffstock->update([
                'stuff_id' => $stuff_id,
                'total_available' => $total_available,
                'total_defec' => $total_defec,
            ]);

            return ApiFormatter::sendResponse(200, true, "berhasil ubah data denga id $id");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function deleted()
    {
        try {
            $stocks = Stuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, "lihat data barang yang dihapus", $stocks);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }


    public function destroy($id){
        try {
            $stuffstock = StuffStock::findORFail($id);

            $stuffstock->delete();

            return ApiFormatter::sendResponse(200, true, "berhasil hapus data barang dengan id $id");

        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restore($id)
    { 
        try {
        $stock = StuffStock::onlyTrashed()->findORFail($id);

        $has_stock->StuffStock::where('stuff_id', $stock->stuff_id)->get();

        if($has_stock->count() == 1) {
            $message = "data sudah ada, tidak boleh di duplikat, silangkah update id stock $stock->stuff_id";
        } else {
            $stock->restore();
            $message = "Berhasil mengembalikan data yang telah dihapus";
        }

            return ApiFormatter::sendResponse(200, true, "berhasil mengembalikan data yang telah dihapus!! yeay", ['id' => $id, 'stuff_id' => $stocks->stuff_id]);

        } catch (\Throwable $th) {

            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());

        }
    }

    public function restoreAll($id)
    {

        try { 
            $stocks = StuffStock::onlyTrashed()->restore();

            return ApiFormatter::sendResponse(200, true, "berhasil mengembalikan semua data yang telah dihapus!");

        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage()); 
        }
    }

    public function permanentDelete($id)
    {
        try {
            $stuffstock = StuffStock::onlyTrashed()->where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, true, "berhasil hapus permanen data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

}
