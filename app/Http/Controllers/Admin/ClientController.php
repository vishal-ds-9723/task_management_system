<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Http\Requests\Admin\CreateClientLoginRequest;
use App\Http\Requests\Admin\UpdateClientLoginRequest;
use App\Http\Requests\Admin\AddSocialLinkRequest;
use App\Http\Requests\Admin\CreateMonthlyScheduleRequest;
use App\Http\Requests\Admin\UpdateMonthlyScheduleRequest;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\User;
use App\Models\ClientSocialMediaLink;
use App\Models\ClientMonthlySchedule;
use App\Services\Admin\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    protected $service;

    public function __construct(ClientService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->getFilteredClients($request);

        return view('admin.clients', $data);
    }

    public function store(StoreClientRequest $request)
    {
        $this->service->createClient($request->validated());

        return redirect()->route('admin.clients')->with('success', 'Client created successfully.');
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->service->updateClient($client, $request->validated());

        // If the edit came from client-detail page, redirect back there
        if ($request->has('_from_detail') || str_contains(url()->previous(), '/clients/' . $client->id)) {
            return redirect()->route('admin.clients.show', $client)->with('success', 'Client updated successfully.');
        }

        return redirect()->route('admin.clients')->with('success', 'Client updated successfully.');
    }

    public function show(Request $request, Client $client)
    {
        $data = $this->service->getClientDetail($client, $request);

        return view('admin.client-detail', $data);
    }

    public function destroy(Client $client)
    {
        $this->service->deleteClient($client);

        return redirect()->route('admin.clients')->with('success', 'Client deleted successfully.');
    }

    public function createLogin(CreateClientLoginRequest $request, Client $client)
    {
        $this->service->createClientLogin($client, $request->validated());

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client login created successfully.');
    }

    public function updateLogin(UpdateClientLoginRequest $request, Client $client, User $user)
    {
        $this->service->updateClientLogin($user, $request->validated());

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client login updated successfully.');
    }

    public function deleteLogin(Client $client, User $user)
    {
        $this->service->deleteClientLogin($user);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client login removed.');
    }

    public function addSocialLink(AddSocialLinkRequest $request, Client $client)
    {
        $this->service->addSocialLink($client, $request->validated());

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Social media link added successfully.');
    }

    public function deleteSocialLink(Client $client, ClientSocialMediaLink $link)
    {
        if ($link->client_id !== $client->id) abort(404);

        $this->service->deleteSocialLink($link);

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Social media link removed.');
    }

    public function updateSocialLink(Request $request, Client $client, ClientSocialMediaLink $link)
    {
        if ($link->client_id !== $client->id) abort(404);

        $data = $this->validateSocialLinkRow($request->all());
        $link->update($data);

        if ($request->wantsJson()) {
            return response()->json(['link' => $link->fresh()]);
        }
        return redirect()->route('admin.clients.show', $client)->with('success', 'Social link updated.');
    }

    public function addSocialLinksBatch(Request $request, Client $client)
    {
        $rows = $request->validate([
            'links'             => ['required', 'array', 'min:1'],
            'links.*.platform'  => ['required', 'in:instagram,facebook,twitter,linkedin,youtube,tiktok,whatsapp'],
            'links.*.url'       => ['required', 'url:http,https', 'max:500'],
            'links.*.label'     => ['nullable', 'string', 'max:100'],
        ])['links'];

        $created = [];
        foreach ($rows as $row) {
            $created[] = ClientSocialMediaLink::create([
                'client_id' => $client->id,
                'platform'  => strtolower(trim($row['platform'])),
                'url'       => trim($row['url']),
                'label'     => isset($row['label']) ? (trim($row['label']) ?: null) : null,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['count' => count($created), 'links' => $created]);
        }
        return redirect()->route('admin.clients.show', $client)
            ->with('success', count($created) . ' social link(s) added.');
    }

    private function validateSocialLinkRow(array $input): array
    {
        return validator($input, [
            'platform' => ['required', 'in:instagram,facebook,twitter,linkedin,youtube,tiktok,whatsapp'],
            'url'      => ['required', 'url:http,https', 'max:500'],
            'label'    => ['nullable', 'string', 'max:100'],
        ])->validate();
    }

    public function getSocialLinksApi(Client $client)
    {
        $response = $this->service->getSocialLinksApi($client);

        return $response;
    }

    public function createMonthlySchedule(CreateMonthlyScheduleRequest $request, Client $client)
    {
        $this->service->createMonthlySchedule($client, $request->validated());

        return redirect()->route('admin.clients.show', [
            'client' => $client,
            'month'  => $request->month,
            'year'   => $request->year,
            'tab'    => 'tasks'
        ])->with('success', 'Monthly schedule created successfully.');
    }

    public function updateMonthlySchedule(UpdateMonthlyScheduleRequest $request, Client $client, ClientMonthlySchedule $schedule)
    {
        $this->service->updateMonthlySchedule($schedule, $request->validated());

        return redirect()->route('admin.clients.show', [
            'client' => $client,
            'month'  => $schedule->month,
            'year'   => $schedule->year,
            'tab'    => 'tasks'
        ])->with('success', 'Monthly schedule updated successfully.');
    }

    // ─────────────────────────── Contacts (dynamic) ───────────────────────────

    public function storeContact(Request $request, Client $client)
    {
        $data = $this->validateContactRequest($request);

        $contact = \Illuminate\Support\Facades\DB::transaction(function () use ($client, $data) {
            $isPrimary = ! empty($data['is_primary']);
            if ($isPrimary) {
                ClientContact::where('client_id', $client->id)->update(['is_primary' => false]);
            }
            // If this is the first contact for the client, auto-mark as primary
            if (! $isPrimary && $client->contacts()->count() === 0) {
                $isPrimary = true;
            }

            return ClientContact::create([
                'client_id'  => $client->id,
                'name'       => $data['name'] ?? null,
                'email'      => $data['email'] ?? null,
                'phone'      => $data['phone'] ?? null,
                'role'       => $data['role'] ?? null,
                'is_primary' => $isPrimary,
                'position'   => (int) (ClientContact::where('client_id', $client->id)->max('position') ?? 0) + 1,
            ]);
        });

        $this->syncClientPrimaryContactColumns($client);

        if ($request->wantsJson()) {
            return response()->json(['contact' => $contact]);
        }
        return redirect()->route('admin.clients.show', $client)->with('success', 'Contact added.');
    }

    public function updateContact(Request $request, Client $client, ClientContact $contact)
    {
        if ($contact->client_id !== $client->id) abort(404);

        $data = $this->validateContactRequest($request);

        \Illuminate\Support\Facades\DB::transaction(function () use ($client, $contact, $data) {
            if (! empty($data['is_primary'])) {
                ClientContact::where('client_id', $client->id)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
                $data['is_primary'] = true;
            } else {
                // If the user is unmarking primary AND this was the primary, leave it; we'll re-sync below
                $data['is_primary'] = $contact->is_primary;
            }
            $contact->update($data);
        });

        $this->syncClientPrimaryContactColumns($client);

        if ($request->wantsJson()) {
            return response()->json(['contact' => $contact->fresh()]);
        }
        return redirect()->route('admin.clients.show', $client)->with('success', 'Contact updated.');
    }

    public function destroyContact(Request $request, Client $client, ClientContact $contact)
    {
        if ($contact->client_id !== $client->id) abort(404);

        $wasPrimary = $contact->is_primary;
        $contact->delete();

        if ($wasPrimary) {
            // Promote the next-oldest contact to primary if any remain
            $next = ClientContact::where('client_id', $client->id)->orderBy('position')->orderBy('id')->first();
            if ($next) {
                $next->update(['is_primary' => true]);
            }
        }

        $this->syncClientPrimaryContactColumns($client);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->route('admin.clients.show', $client)->with('success', 'Contact removed.');
    }

    public function setPrimaryContact(Request $request, Client $client, ClientContact $contact)
    {
        if ($contact->client_id !== $client->id) abort(404);

        \Illuminate\Support\Facades\DB::transaction(function () use ($client, $contact) {
            ClientContact::where('client_id', $client->id)->update(['is_primary' => false]);
            $contact->update(['is_primary' => true]);
        });

        $this->syncClientPrimaryContactColumns($client);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->route('admin.clients.show', $client)->with('success', 'Primary contact updated.');
    }

    private function validateContactRequest(Request $request): array
    {
        return $request->validate([
            'name'       => ['nullable', 'string', 'max:255'],
            'email'      => ['nullable', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:50', 'regex:/^\+?[0-9\s\-\(\)]{7,20}$/'],
            'role'       => ['nullable', 'string', 'max:100'],
            'is_primary' => ['nullable', 'boolean'],
        ], [
            'phone.regex' => 'Phone must contain only digits, spaces, +, -, or parentheses and be 7-20 characters.',
        ]);
    }

    /**
     * Keep the legacy contact_person/email/phone columns in sync with the primary contact
     * so existing code reading those columns still works.
     */
    private function syncClientPrimaryContactColumns(Client $client): void
    {
        $primary = ClientContact::where('client_id', $client->id)->where('is_primary', true)->first();

        $client->update([
            'contact_person' => $primary?->name  ?? null,
            'contact_email'  => $primary?->email ?? null,
            'contact_phone'  => $primary?->phone ?? null,
        ]);
    }
}

