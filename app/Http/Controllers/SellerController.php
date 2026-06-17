<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSellerRequest;
use App\Http\Requests\UpdateSellerRequest;
use App\Models\Seller;
use Illuminate\Support\Facades\Storage;

class SellerController extends Controller
{
    public function index()
    {
        return view('sellers.index', ['sellers' => Seller::latest()->paginate(15)]);
    }

    public function create()
    {
        return view('sellers.create', ['seller' => new Seller]);
    }

    public function store(StoreSellerRequest $request)
    {
        Seller::create($this->validatedData($request));

        return redirect()->route('sellers.index')->with('success', 'تم حفظ البائع.');
    }

    public function show(Seller $seller)
    {
        return view('sellers.show', compact('seller'));
    }

    public function edit(Seller $seller)
    {
        return view('sellers.edit', compact('seller'));
    }

    public function update(UpdateSellerRequest $request, Seller $seller)
    {
        $data = $this->validatedData($request, $seller);
        $seller->update($data);

        return redirect()->route('sellers.index')->with('success', 'تم تحديث البائع.');
    }

    public function destroy(Seller $seller)
    {
        if ($seller->logo_path) {
            Storage::disk('public')->delete($seller->logo_path);
        }
        $seller->delete();

        return redirect()->route('sellers.index')->with('success', 'تم حذف البائع.');
    }

    private function validatedData(StoreSellerRequest|UpdateSellerRequest $request, ?Seller $seller = null): array
    {
        $data = $request->validated();
        $data['is_default'] = $request->boolean('is_default');
        unset($data['logo']);
        if (blank($data['jofotara_secret_key'] ?? null)) {
            unset($data['jofotara_secret_key']);
        }

        if ($request->hasFile('logo')) {
            if ($seller?->logo_path) {
                Storage::disk('public')->delete($seller->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('sellers', 'public');
        }

        if ($data['is_default']) {
            Seller::where('is_default', true)->when($seller, fn ($query) => $query->whereKeyNot($seller->id))->update(['is_default' => false]);
        }

        return $data;
    }
}
