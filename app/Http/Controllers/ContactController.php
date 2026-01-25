<?php

namespace App\Http\Controllers;

use Couchbase\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\models\Contact;

class ContactController extends Controller
{
    private function validator(Request $request) {
        return Validator::make($request->all(), [
            'given_name' => 'required',
            'family_name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'required',
            'user_id' => 'required|exists:users,id',
        ],
        [
            'given_name.required' => __('Le prénom est requis'),
            'family_name.required' => __('Le nom est requis'),
            'email.email' => __('L\'email n\'est pas valide'),
            'phone.required' => __('Le numéro de teléphone est requis'),
            'user_id.required' => __('L\'ID de compte est requis'),
            'user_id.exists' => __('L\'ID de compte est invalide')
        ]);
    }


    public function create(Request $request) {
        $user = $request->user();
        $validator = $this->validator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator->errors())->withInput();
        }
        $values = $validator->validated();
        if ($values['user_id'] != $user->id) {
            return back()->withErrors(__('L\'ID de compte est invalide'))->withInput();
        }
        try {
            $contact = Contact::create([
                'given_name' => $values['given_name'],
                'family_name' => $values['family_name'],
                'email' => $values['email'] ?? null,
                'phone' => $values['phone'],
                'user_id' => $values['user_id']
            ]);
            return back()->with(['success' => $contact]);
        }
        catch (QueryException $e){
            return back()->withErrors(['error' => $e])->withInput();
        }
    }

    public function update(Request $request) {
        $validator = $this->validator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator->errors())->withInput();
        }
        $values = $validator->validated();
        try {

            $contact = Contact::find($request->input('id'));
            if (!$contact) {
                return back()->withErrors(['error' => 'cant find contact'])->withInput();
            }
            $contact->given_name = $values['given_name'];
            $contact->family_name = $values['family_name'];
            $contact->email = $values['email'] ?? null;
            $contact->phone = $values['phone'];
            $contact->save();
            return back()->with(['success' => $contact]);
        }
        catch (QueryException $e){
            return back()->withErrors(['error' => $e])->withInput();
        }
    }

    public function destroy(Request $request) {
        $user = $request->user();
        try {
            $contact = Contact::find($request->input('id'));
            if (!$contact) {
                return back()->withErrors(['error' => 'cant find contact'])->withInput();
            }
            if ($contact->user_id == $user->id) {
                $contact->delete();
                return back();
            }
            else {
                return back()->withErrors(['error' => 'You cannot delete this contact.']);
            }
        }
        catch (\Illuminate\Database\QueryException | ModelNotFoundException $e) {
            return back()->withErrors(['can\'t delete this contact.']);
        }
    }
}
