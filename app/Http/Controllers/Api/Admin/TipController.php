<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TipController extends Controller
{
    use ApiResponser;

    public function index()
    {
        return $this->success(Tip::latest()->get(), 'Data tip harian berhasil dimuat');
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $tip = Tip::create($request->only(['content', 'is_active']));

        return $this->success($tip, 'Tip harian berhasil dibuat', 201);
    }

    public function show($id)
    {
        $tip = Tip::findOrFail($id);
        return $this->success($tip, 'Detail tip harian');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'string',
            'is_active' => 'boolean',
        ]);

        $tip = Tip::findOrFail($id);
        $tip->update($request->only(['content', 'is_active']));

        return $this->success($tip, 'Tip harian berhasil diperbarui');
    }

    public function destroy($id)
    {
        $tip = Tip::findOrFail($id);
        $tip->delete();

        return $this->success(null, 'Tip harian berhasil dihapus');
    }
}
