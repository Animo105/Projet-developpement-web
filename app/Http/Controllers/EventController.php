<?php
namespace App\Http\Controllers;
use App\Models\Bill;
use App\Models\Event;
use App\Models\Inscriptions;
use App\Models\Tarif;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Enums\TarifType;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Nette\Schema\ValidationException;

class EventController extends Controller
{
    private function get_unlinked_events(int $event_id = -1) {
        $events = Event::where('start_date', '>', Carbon::now())
            ->where(function ($query) use ($event_id) {
                $query->where('event_id', $event_id)
                    ->orWhere('event_id', null);
            })
            ->orderBy('name', 'asc')
            ->get()->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                ];
            });
        return (array_merge([['id' => null, 'name' => 'Aucun']], $events->toArray()));
}
    private function format(Event $event) {
        return [
            'id' => $event->id,
            'title' => $event->name,
            'description' => $event->description,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'date' => $event->dateFormat(),
            'end_date_registration' => $event->end_date_registration,
            'profits' => $event->amount(),
            'event_linked' => $event->event_id ?? null,
            'tarifs' => $event->tarifs()->get()->map(fn ($tarif) => [
                'id' => $tarif->id,
                'label' => TarifType::labels()[$tarif->type] ?? $tarif->type,
                'type' => $tarif->type,
                'amount' => $tarif->price,
            ])->values()->toArray(),
            'entries' => [],
            'spendings' => [],
        ];
    }

    private static $ListInputs = [
        'participant_price' => TarifType::Participant->value,
        'accompagnateur_price' => TarifType::Accompagnateur->value,
        'weapon_price' => TarifType::Arme->value,
        'costume_price' => TarifType::Costume->value,
        'rabais_fratrie' => TarifType::RabaisFratrie->value,
        'rabais_event' => TarifType::RabaisEvent->value,
    ];

    private function validateEvent(Request $request)
    {
        // rend les champs dans la variable négatif (rabais)
        $var = [
            'rabais_fratrie', 'rabais_event'
        ];
        foreach ($var as $value) {
            if ($request->has($value)) {
                $input = $request->input($value);
                if ($input == 0) {
                    // le supprimer
                    $request->request->remove($value);
                } elseif ($input > 0) {
                    // le rendre négatif
                    $request->merge([$value => -abs($input)]);
                }
            }
        }

        return Validator::make($request->all(), [
            'title' => ['required', 'min:2', 'max:64'],
            'description' => ['nullable'],
            'start_date' => ['required', 'date','before:end_date', 'after:tomorrow'],
            'end_date' => 'required|date|after:date_start',
            'date_registration' => 'required|date|after:tomorrow|before:start_date',
            'weapon_price' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            'costume_price' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            'participant_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'accompagnateur_price' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            'rabais_fratrie' => 'nullable|numeric|max:0',
            'event_linked' => 'nullable|numeric|exists:events,id',
            'rabais_event' => 'required_with:event_linked|nullable|numeric|max:0'
        ],
        [
            'title.required' => __('Le titre est obligatoire'),
            'title.min' => __('Le titre doit contenir au minimum :min'),
            'title.max' => __('Le titre doit contenir au maximum :max'),
            'start_date.required' => __('La date de début est obligatoire'),
            'start_date.after' => __('La date de début dois être dans le future'),
            'start_date.before' => __('La date de début dois être avant la fin'),
            'end_date.required' => __('La date de fin est obligatoire'),
            'end_date.after' => __('La date de fin doit être apres le début'),
            'date_registration.required' => __('La date de registrement est obligatoire'),
            'date_registration.after' => __('La date limite d\'inscription dois être dans le future'),
            'date_registration.before' => __('La date limite d\'inscription dois être avant que l\'évènement commence.'),
            'participant_price.required' => __('Le prix du participant est obligatoire'),
            'rabais_fratrie.max' => __('Le rabais de fratrie dois être négatif'),
            'event_linked.numeric' => __('L\'évènement lier n\'est pas valide'),
            'event_linked.exists' => __('L\'évènement lier n\'est pas valide'),
            'rabais_event.required_with' => __('Le rabais est requit en cas de lien'),
            'rabais_event.numeric' => __('Le rabais n\'est pas valide'),
            'rabais.max' => __('Le rabais doit etre négatif'),
            'regex' => __('Les champs numérique dois contenir un nombre.')
        ]);
    }

    public function create(){
        return Inertia::render('Events/create',
            ['linkableEvents' => $this->get_unlinked_events()]);
    }

    public function edit($id){
        $event = Event::find($id);
        if (!$event) {
            return back()->withErrors(["Event introuvable"]);
        }
        $formatedEvent = $this->format($event);
        return Inertia::render('Events/edit', ['event' => $formatedEvent,
        'linkableEvents' => $this->get_unlinked_events($event->id)]);
    }

    public function index() {
        $events = Event::orderBy('start_date')->get();
        [$future, $past] = $events->partition(fn ($event) => $event->start_date > Carbon::now());
        $past = $past->reverse();
        $events = [
            'future' => $future->map(fn ($event) => [
                'id' => $event->id,
                'title' => $event->name,
                'description' => $event->description,
                'date' => $event->dateFormat(),
                'participants' => $event->entries()->count(),
                'can_register' => $event->end_date_registration > Carbon::now()
            ])->values(),

            'past' => $past->map(fn ($event) => [
                'id' => $event->id,
                'title' => $event->name,
                'description' => $event->description,
                'date' => $event->dateFormat(),
                'participants' => $event->entries()->count(),
                'can_register' => false
            ])->values(),
        ];
        return Inertia::render('Events/index', ['future_events' => $events['future'], 'past_events' => $events['past']]);
    }

    public function show(Request $request, int $id) {
        $user = $request->user();
        $event = Event::find($id);
        if(!$event) {
            return redirect()->route('events.index');
        }

        if (!$user->isAdmin() && $event->end_date < Carbon::now()->day(-1)) {
                return redirect()->route('events.index');
        }
        $participants = [];
        $formatedBill = null;
        $formatedEvent = $this->format($event);

        if ($user->isAdmin()) {
            $formatedEvent['entries'] = $event->entries()->map(fn ($entry) => [
                'participant_id' => $entry['participant']->id,
                'participant_name' => $entry['participant']->family_name . " " . $entry['participant']->given_name,
                'character_name' => $entry['participant']->character?->name ?? 'Default Name',
                'character_id' => $entry['participant']->character?->id ?? 0,
                'telephone_number' => $entry['participant']->user->phone,
                'is_adult' => $entry['participant']->is_adult(),
                'types' => $entry['types']
            ]);
            $formatedEvent['spendings'] = $event->spendings()->get()->map(fn ($spending) => [
                'id' => $spending->id,
                'name' => $spending->name,
                'amount' => $spending->amount,
            ]);
        }
        else {
            $participants = $user->participants()->get()->map(fn ($participant) => [
                'id' => $participant->id,
                'name' => $participant->family_name . " " . $participant->given_name,
                'character_name' => $participant->character?->name ?? "Default Name",
                'is_adult' => $participant->is_adult()
            ]);
            //enleve les adultes si pas de tarifs accompagnateur
            if (!$event->getTarif(TarifType::Accompagnateur)) {
                $participants = $participants->filter(fn ($participant) => !$participant['is_adult']);
            }
            $participants->toArray();

            $bills = $user->get_bills_for_event($id) ?? [];
            $tempEntries = [];
            $tempLocked = [];
            foreach ($bills as $b) {
                foreach ($user->participants as $participant) {
                    $billEntry = [];
                    foreach ($b->inscriptionsForParticipant($participant->id)->get() as $inscription) {
                        if ($inscription->tarifType() == "participant" || $inscription->tarifType() == "accompagnateur") {
                            $billEntry["inscrit"] = true;
                        }
                        else {
                            $billEntry[$inscription->tarifType()] = true;
                        }
                    }
                    if ($billEntry != []) {
                        $tempEntries[$participant->id] = $billEntry;
                        if ($b->status_id != 3) { //3 = en attente de vérification
                            $tempLocked[$participant->id] = $billEntry;
                        }
                    }
                }
            }
            $formatedBill['entries'] = $tempEntries;
            $formatedBill['locked'] = $tempLocked;
        }

        return Inertia::render('Events/show', ['event' => $formatedEvent, 'participants' => $participants, 'bill' => $formatedBill]);
    }

    public function store(Request $request) {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return redirect()->route('events.index');
        }
        $validator = $this->validateEvent($request);
        if($validator->fails()) {
            return back()->withErrors($validator->errors())->withInput();
        }


        try {
            DB::beginTransaction();
            $values = $validator->validated();
            $event = Event::create([
                'name' => $values['title'],
                'description' => $values['description'] ?? null,
                'start_date' => $values['start_date'],
                'end_date' => $values['end_date'],
                'end_date_registration' => $values['date_registration'],
                'event_id' => $values['event_linked'] ?? null,
            ]);

            if (isset($values['event_linked'])) {
                $otherEvent = Event::findOrFail($values['event_linked']);
                $otherEvent->update(['event_id' => $event->id]);
                Tarif::create([
                    'event_id' => $otherEvent->id,
                    'type' => TarifType::RabaisEvent,
                    'price' => $values['rabais_event'],
                ]);
            }
            foreach (EventController::$ListInputs as $input => $type ) {
                if (isset($values[$input])) {
                    Tarif::create([
                        'event_id' => $event->id,
                        'type' => $type,
                        'price' => $values[$input],
                    ]);
                }

            }
            DB::commit();
        }catch (QueryException | ValidationException $e) {
            DB::rollBack();
            return back()->withErrors(["Une erreur s'est produite lors de la création"]);        }
        return redirect()->route('events.index');

    }

    public function destroy(Request $request) {
        $user = $request->user();
        $id = request('id');
        if (!$id || !is_numeric($id)) {
            return back()->withErrors(["mauvaise ID"]);
        }
        $event = Event::find($request->input('id'));
        if (!$event) {
            return back()->withErrors(["L'event n'existe pas"]);
        }
        if ($user->isAdmin()) {
            try {
                $event->delete();
            }
            catch (QueryException | ValidationException $e) {
                return back()->withErrors(["Erreur lors de la suppression"]);
            }

        }
        return back()->withErrors(["Erreur lors de la suppression"]);
    }

    public function inscription(Request $request, int $id) {
        $user = $request->user();
        $data = $request->post();
        if ($data == []) {
            return back()->withErrors("Veuillez inscrire au moins un participant");
        }
        $event = Event::find($id);
        if (!$event) {
            return back()->withErrors("Aucun event pour l'id trouvé");
        }
        foreach ($data as $key => $value) {
            if (!$user->has_participant($key)) {
                return back()->withErrors("un des participant n'existe pas");
            }
        }

        try {
            DB::beginTransaction();

            $bills = $user->get_bills_for_event($id);
            $bill = null;
            foreach ($bills as $b) {
                if ($b->status_id == 3) { //3 = en attente de vérification
                    $bill = $b;
                    break;
                }
            }
            if (!$bill) {
                $bill = new Bill();
                $bill->user_id = $user->id;
                $bill->event_id = $id;
                $bill->status_id = 3; //en attente de vérification
                $bill->due_date = null;
                $bill->save();
            }

            if (!$bill->inscriptions->isEmpty()) {
                foreach ($bill->inscriptions as $location) {
                    $location->delete();
                }
            }

            $inscriptions = [];
            foreach ($data as $participant_id => $value) {
                foreach ($value as $tarif_type) {
                    $tarif = $event->getTarif($tarif_type);
                    if (!$tarif) {
                        DB::rollBack();
                        return back()->withErrors("Le tarif n'existe pas");
                    }
                    $inscription = Inscriptions::create([
                        'participant_id' => $participant_id,
                        'date' => now()->format('Y-m-d'),
                        'bill_id' => $bill->id,
                        'tarif_id' => $tarif->id,
                        'pay_price' => $tarif->price,
                    ]);
                    $inscriptions[] = $inscription;
                }
                //check pour linked event
            }
            if ($inscriptions == []) {
                $bill->delete();
                DB::rollBack();
            }
            else {
                DB::commit();
            }
            return redirect()->route('events.index');
        }
        catch (QueryException | ValidationException $e) {
            DB::rollBack();
            return back()->withErrors("Erreur pendant l'inscription");
        }

    }

    public function update(Request $request, int $id) {
        $validator = $this->validateEvent($request);
        if ($validator->fails()) {
            return back()->withErrors($validator->errors())->withInput();
        }

        $event = Event::find($id);
        if (!$event) {
            return back()->withErrors(["L'event n'existe pas"]);
        }
        $values = $validator->validated();
        try {
            DB::beginTransaction();

            if (isset($event->event_id) && $event->event_id != null) {
                $otherEvent = Event::findOrFail($event->event_id);
                $otherEvent->getTarif(TarifType::RabaisEvent)->delete();
                $otherEvent->update(['event_id' => null]);
            }

            $event->update([
                'name' => $values['title'],
                'description' => $values['description'] ?? null,
                'start_date' => $values['start_date'],
                'end_date' => $values['end_date'],
                'end_date_registration' => $values['date_registration'],
                'event_id' => $values['event_linked'] ?? null,
            ]);

            if (isset($values['event_linked'])) {
                $otherEvent = Event::findOrFail($values['event_linked']);
                $otherEvent->update(['event_id' => $event->id]);
                Tarif::create([
                    'event_id' => $otherEvent->id,
                    'type' => TarifType::RabaisEvent,
                    'price' => $values['rabais_event'],
                ]);
            }
            foreach (EventController::$ListInputs as $input => $type ) {

                if (isset($values[$input])) {
                    if ($event->hasTarif($type)) {
                        $event->getTarif($type)->update([
                            'price' => $values[$input],
                        ]);
                    }
                    else {
                        Tarif::create([
                            'event_id' => $event->id,
                            'type' => $type,
                            'price' => $values[$input],
                        ]);
                    }
                } else {
                    if ($event->hasTarif($type)) {
                        $event->getTarif($type)->delete();
                    }
                }
            }

            DB::commit();

            return redirect()->route('bills.show', $id);
        }
        catch (QueryException | ValidationException $e) {
            DB::rollBack();
            return back()->withErrors(["Erreur lors de la modification"]);
        }
    }
}
