<?php

namespace App\Http\Controllers;

use App\Models\Spending;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpendingController extends Controller
{
    private function validate(Request $request) {
        return Validator::make($request->all(), [
            'name' => 'required',
            'event_id' => 'required|exists:events,id',
            'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
        ],
            [
                'name.required' => __('Nom est requit'),
                'event_id.required' => __('Id d\'évènement est requit'),
                'event_id.exists' => __('Évènement invalide'),
                'amount.required' => __('Montant est requit'),
                'amount.regex' => __('Le format est invalide'),
            ]);
    }
    function store(Request $request) {
        $validator = $this->validate($request);

        if ($validator->fails()) {
            back()->withErrors([$validator->errors()])->withInput();
        }
        $values = $validator->validated();
        try {
            $spending = Spending::create($values);
            return back();

        } catch (QueryException $e) {
            return back()->withErrors(['danger' => __('une erreur s\'est produit')]);
        }
    }

    public function update(Request $request) {
        $id = $request->get("id");
        if (!$id) {
            return back()->withErrors(['danger' => __('une erreur s\'est produit')]);
        }
        $validator = $this->validate($request);
        if ($validator->fails()) {
            back()->withErrors([$validator->errors()])->withInput();
        }

        try {
            $values = $validator->validated();
            $spending = Spending::findOrFail($id);
            $spending->update($values);
            return back();

        } catch (QueryException $e) {
            return back()->withErrors(['danger' => __('une erreur s\'est produit')]);
        }
    }

    public function destroy(Request $request) {
        try {
            $id = $request->input('id');
            $spending = Spending::findOrFail($id);
            $spending->delete();
            return back();
        } catch (QueryException $e) {
            return back()->withErrors(['danger' => __('une erreur s\'est produit')]);
        }
    }
}
