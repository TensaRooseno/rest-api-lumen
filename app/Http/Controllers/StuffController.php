<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stuff;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiFormatter;

class StuffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $stuff = Stuff::with('stuffstock')->get();

        return ApiFormatter::sendResponse(200, true, 'Lihat semua barang', $stuff);
        // return response()->json([
        //     'success' => true,
        //     'message' => 'lihat semua barang',
        //     'data' => $stuff
        // ], 200);
    }

    public function store(Request $request)
    {

        try {
            $this->validate($request, [
                'name' => 'required',
                'category' => 'required',
            ]);
            $stuff = Stuff::create([
                'name' => $request->input('name'),
                'category' => $request->input('category'),
            ]);
            return ApiFormatter::sendResponse(201, true, 'Barang berhasil disimpan!', $stuff);
        } catch (\throwable $th) {
            if ($th->validator->errors()) {
                return ApiFormatter::sendResponse(400, false, 'Terdapat kesalahan input, Silahkan coba lagi!', $th->validator->errors());
            } else {
                return ApiFormatter::sendResponse(400, false, 'Terdapat kesalahan input, Silahkan coba lagi!', $th->getMessage());
            }
        }

        // $validator = Validator::make($request->all(), [
        //     'name' => 'required',
        //     'category' => 'required',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'semua kolom wajib di isi',
        //         'data' => $validator->errors()
        //     ], 400);
        // } else {
        //     $stuff = Stuff::create([
        //         'name' => $request->input('name'),
        //         'category' => $request->input('category'),
        //     ]);

        //         if ($stuff) {
        //             return response()->json([
        //                 'success' => true,
        //                 'message' => 'barang berhasil disimpan',
        //                 'data' => $stuff,
        //             ], 201);
        //         } else {
        //             return response()->json([
        //                 'success' => false,
        //                 'message' => 'barang gagal disimpan',
        //             ], 400);
        //         }
        //     }
    }

    public function show($id)
    {
        try {
            $stuff = Stuff::with('stuffstock')->findOrFail($id); // Pastikan menggunakan 'stuffstock' bukan 'stock'
            return ApiFormatter::sendResponse(200, true, "lihat barang dengan id $id", $stuff);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return ApiFormatter::sendResponse(404, false, "data dengan id $id tidak ditemukan");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(500, false, "Terjadi kesalahan dalam menampilkan data", $th->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $stuff = Stuff::findORFail($id);
            $name = ($request->name) ? $request->name : $stuff->name;
            $category = ($request->category) ? $request->category : $stuff->category;

            $stuff->update([
                'name' => $name,
                'category' => $category
            ]);

            return ApiFormatter::sendResponse(200, true, "berhasil ubah data denga id $id");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $stuff = Stuff::findORFail($id);

            $stuff->delete();

            return ApiFormatter::sendResponse(200, true, "berhasil hapus data barang dengan id $id");

        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function deleted()
    {
        try {
            $stuff = Stuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, "lihat data barang yang dihapus", $stuff);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $stuff = Stuff::onlyTrashed()->where('id', $id);

            $stuff->restore();

            return ApiFormatter::sendResponse(200, true, "berhasil mengembalikan data yang telah dihapus!! yeay", ['id' => $id]);

        } catch (\Throwable $th) {

            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());

        }
    }

    public function restoreAll($id)
    {

        try { 
            $stuff = Stuff::onlyTrashed();

            $stuff->restore();

            return ApiFormatter::sendResponse(200, true, "berhasil mengembalikan semua data yang telah dihapus!");

        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage()); 
        }
    }

    public function permanentDelete($id)
    {
        try {
            $stuff = Stuff::onlyTrashed()->where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, true, "berhasil hapus permanen data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }

    }

}