<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return AddressResource::collection($addresses);
    }

    public function store(AddressRequest $request)
    {
        $data = $request->validated();
        $isDefault = $data['is_default'] ?? ($request->user()->addresses()->count() === 0);

        if ($isDefault) {
            $this->unsetDefaults($request->user()->id);
        }

        $address = $request->user()->addresses()->create(array_merge($data, [
            'is_default' => $isDefault,
        ]));

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    public function update(AddressRequest $request, Address $address)
    {
        $this->authorizeOwnership($request, $address);

        $data = $request->validated();
        $isDefault = (bool) ($data['is_default'] ?? false);

        if ($isDefault) {
            $this->unsetDefaults($request->user()->id);
        } elseif (!$address->is_default) {
            $data['is_default'] = false;
        }

        $address->update($data);

        return new AddressResource($address);
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorizeOwnership($request, $address);

        $wasDefault = (bool) $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $replacement = $request->user()->addresses()->first();
            if ($replacement !== null) {
                $replacement->update(['is_default' => true]);
            }
        }

        return response()->json(['data' => ['message' => 'Address deleted.']]);
    }

    public function setDefault(Request $request, Address $address)
    {
        $this->authorizeOwnership($request, $address);

        $this->unsetDefaults($request->user()->id);
        $address->update(['is_default' => true]);

        return new AddressResource($address);
    }

    protected function authorizeOwnership(Request $request, Address $address): void
    {
        if ((int) $address->user_id !== (int) $request->user()->id) {
            abort(404, 'Address not found.');
        }
    }

    protected function unsetDefaults(int $userId): void
    {
        Address::where('user_id', $userId)->where('is_default', true)->update(['is_default' => false]);
    }
}
