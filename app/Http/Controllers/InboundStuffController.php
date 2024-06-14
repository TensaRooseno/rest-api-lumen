<?php

namespace App\Http\Controllers;

use App\models\InboundStuff;
use App\models\Stuff;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\models\StuffStock;
use App\Helpers\ApiFormatter; 

class InboundStuffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function index(){
        $inboundStuff = InboundStuff::with('stuff.stuffstock')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lihat semua barang',
            'data' => $inboundStuff
        ],200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stuff_id' => 'required',
            'total' => 'required',
            'date' => 'required',
            'proff_file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Semua kolom wajib disi!',
                'data' => $validator->errors()
            ], 400);
        } else {
            $file = $request->file('proff_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(app()->basePath('public/uploads'), $filename);

            $inboundStuff = InboundStuff::create([
                'stuff_id' => $request->input('stuff_id'),
                'total' => $request->input('total'),
                'date' => $request->input('date'),
                'proff_file' => $filename,
            ]);

            
            $stuffStock = StuffStock::where('stuff_id', $request->input('stuff_id'))->first();
            if ($stuffStock) {
                $stuffStock->total_available += $request->input('total');
                $stuffStock->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'data' => $inboundStuff
            ], 201);
        }
    }
    public function show($id){
        try{
            $inboundStuff = InboundStuff::findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => "Lihat Barang dengan id $id",
                'data' => $inboundStuff
            ],200);
        } catch(Exception $th) {
            return response()->json([
                'success' => false,
                'message' => "Data dengan id $id tidak ditemukan",
            ],400);
        }
    }
    
    public function update(Request $request, $id){
        try{
            $inboundStuff = InboundStuff::findOrFail($id);
            $stuff_id = ($request->stuff_id) ? $request->stuff_id : $inboundStuff->stuff_id;
            $total = ($request->total)? $request->total : $inboundStuff->total;
            $date = ($request->date)? $request->date : $inboundStuff->date;
            
            if ($request->hasFile('proff_file')) {
                $file = $request->file('proff_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(app()->basePath('public/uploads'), $filename);
            } else {
                $filename = $inboundStuff->proff_file;
            }
    
            $inboundStuff->update([
                'stuff_id' => $stuff_id,
                'total' => $total,
                'date' => $date,
                'proff_file' => $filename,
            ]);
    
            return response()->json([
                'success' => true,
                'message' => "Barang berhasil diubah Data dengan id $id",
                'data' => $inboundStuff
            ],200);
        } catch(\Throwable $th){
            return response()->json([
                'success' => false,
                'message' => "Proses gagal! data dengan id $id tidak ditemukan",
            ],400);
        }
    }
    
    public function destroy($id){
        try{
            $inboundStuff = InboundStuff::findOrFail($id);
            $stock = StuffStock::where('stuff_id', $inboundStuff->stuff_id)->first();
            
            $available_min = $stock->total_available - $inboundStuff->total;
            $available = ($available_min < 0) ? 0 : $available_min;
            $defect = ($available_min < 0) ? $stock->total_defect + ($available * -1) : $stock->total_defect;
            $stock->update([
                'total_available' => $available,
                'total_defect' => $defect
            ]);
    
            $inboundStuff->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Barang Hapus Data dengan id: $id",
                'data' => $inboundStuff
            ],200);
        } catch(\Throwable $th){
            return response()->json([
                'success' => false,
                'message' => "Proses gagal! data dengan id $id tidak ditemukan",
            ],400);
        }
    }

    public function restoreAll()
    {
        try {
            $inbounds = InboundStuff::onlyTrashed();

            foreach ($inbounds->get() as $inbound) {
                $stock = StuffStock::where('stuff_id', $inbound->stuff_id)->first();

                $available = $stock->total_available + $inbound->total;
                $available_min = $inbound->total - $stock->total_available;
                $defect = ($available_min < 0) ? $stock->total_defect + ($available_min * -1) : $stock->total_defect;

                $stock->update([
                    'total_available' => $available,
                    'total_defect' => $defect
                ]);
            }

            $inbounds->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan semua data yang telah di hapus!");
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! Silakan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDel($id)
    {
        try {
            $inboundStuff = inboundStuff::onlyTrashed()->where('id', $id)->first();
            


            if ($inboundStuff) {
                $imageName = $inboundStuff->proff_file;
                $check = inboundStuff::onlyTrashed()->where('id', $id)->get();
                File::delete('uploads/' . $imageName);
                $inboundStuff->forceDelete();
                return ApiFormatter::sendResponse(200, true, 'Berhasil menghapus permanen data dengan id = ' . $id . 'dan berhasil menghapus semua data permanent dengan file name: ' . $imageName, $check);
            } else {
                return ApiFormatter::sendResponse(200, true, 'Bad request');
            }

        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
        }
    }

    public function permanentDelAll()
    {
        try {
            $inboundStuff = inboundStuff::onlyTrashed()->forceDelete();
            if ($inboundStuff) {
                return ApiFormatter::sendResponse(200, true, 'Berhasil menghapus permanen semua data');
            } else {
                return ApiFormatter::sendResponse(400, false, 'bad request');
            }
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
        }
    }

    public function dashboardCalculate()
    {
        try {
            $count = InboundStuff::where('total', '>', 10)->count();
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    
}
