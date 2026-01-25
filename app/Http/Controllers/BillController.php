<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Event;
use App\Models\Method;
use App\Models\Payment;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BillController extends Controller
{
    public function index()
    {
        if (Auth::user()->isAdmin()) {
            $bills = Bill::with([
                'inscriptions.tarif.event',
                'inscriptions',
                'status',
                'user',
                'payments',
                'participants',
            ])->get();
        } else {
            $bills = Bill::where('user_id', Auth::id())->with(['inscriptions.tarif.event', 'status', 'payments', 'participants', 'user'])->get();
        }


        $events = Event::all();
        $statuses = Status::all();


        return Inertia::render('Bills/Index', [
            'bills' => $bills,
            'events' => $events,
            'statuses' => $statuses,
        ]);
    }

    public function show($id)
    {
        $bill = Bill::with(['tarifs', 'payments', 'status', 'participants', 'inscriptions.tarif.event'])->findOrFail($id);

        $reduction = 0;

        $inscription = [
            'amountChild' => 0,
            'childBasePrice' => 0,
            'amountAdult' => 0,
            'adultBasePrice' => 0,
            'price' => 0,
        ];

        $weapons = [
            'amount' => 0,
            'basePrice' => 0,
            'price' => 0,
        ];

        $costumes = [
            'amount' => 0,
            'basePrice' => 0,
            'price' => 0,
        ];

        $formatPrice = fn($price) => $price == floor($price) ? intval($price) : number_format($price, 2);

        foreach ($bill->tarifs as $tarif) {
            switch ($tarif->type) {
                case 'participant':
                    $inscription['amountChild']++;
                    $inscription['childBasePrice'] = $formatPrice($tarif->price);
                    $inscription['price'] += $tarif->price;
                    break;

                case 'accompagnateur':
                    $inscription['amountAdult']++;
                    $inscription['adultBasePrice'] = $formatPrice($tarif->price);
                    $inscription['price'] += $tarif->price;
                    break;

                case 'arme':
                    $weapons['amount']++;
                    $weapons['basePrice'] = $formatPrice($tarif->price);
                    $weapons['price'] += $tarif->price;
                    break;

                case 'costume':
                    $costumes['amount']++;
                    $costumes['basePrice'] = $formatPrice($tarif->price);
                    $costumes['price'] += $tarif->price;
                    break;

                case 'rfratrie':
                    $reduction = $formatPrice($tarif->price);
                    break;
            }
        }

        $subtotal = $inscription['price'] + $weapons['price'] + $costumes['price'];
        $total = $subtotal - $reduction;

        $payments = $bill->payments()->orderBy('date')->get();
        $paymentMethods = Method::all();
        $statuses = Status::all();
        $participants = $bill->participants;

        return Inertia::render('Bills/Show', [
            'bill' => $bill,
            'inscription' => $inscription,
            'participants' => $participants,
            'weapons' => $weapons,
            'costumes' => $costumes,
            'subtotal' => $subtotal,
            'reduction' => $reduction,
            'total' => $total,
            'payments' => $payments,
            'paymentMethods' => $paymentMethods,
            'statuses' => $statuses,
        ]);
    }

    public function addPayment(Request $request, Bill $bill)
    {
        if (!Auth::user()->isAdmin() && $bill->user_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'method' => 'required|exists:methods,id',
        ]);

        $payment = Payment::create([
            'bill_id' => $bill->id,
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'method_id' => $validated['method'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction ajoutée avec succès !',
            'payment' => $payment,
        ]);
    }

    public function updateStatus(Request $request, Bill $bill)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'status_id' => 'required|exists:statuses,id',
        ]);

        $bill->status_id = $validated['status_id'];
        $bill->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour !',
            'status_id' => $bill->status_id,
        ]);
    }

    public function deletePayment(int $paymentId)
    {
        // Vérifier si l'utilisateur peut supprimer
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $payment = Payment::findOrFail($paymentId);
        $payment->delete();

        return response()->json(['success' => true, 'message' => 'Transaction supprimée avec succès !']);
    }
}
