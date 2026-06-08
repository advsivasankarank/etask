<?php

declare(strict_types=1);

namespace Modules\Clients;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\ClientRepository;
use App\Services\ClientService;
use Throwable;

final class ClientController
{
    public function __construct(
        private readonly ClientRepository $clients = new ClientRepository(),
        private readonly ClientService $clientService = new ClientService()
    ) {
    }

    public function index(Request $request): void
    {
        $search = trim((string) $request->input('search', ''));
        $page = max(1, (int) $request->input('page', 1));
        $pagination = $this->clients->paginateSearch($search, false, $page, 12);

        $content = View::render(base_path('modules/Clients/views/index.php'), [
            'title' => 'Clients',
            'activeMenu' => 'clients',
            'clients' => $pagination['items'],
            'pagination' => $pagination,
            'search' => $search,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function create(): void
    {
        $content = View::render(base_path('modules/Clients/views/form.php'), [
            'title' => 'Create Client',
            'activeMenu' => 'clients',
            'mode' => 'create',
            'publicRegistration' => false,
            'client' => null,
            'contact' => null,
            'crmUsers' => $this->clients->crmUsers(),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function store(Request $request): void
    {
        $payload = $request->all();
        Session::flash('old', $payload);

        try {
            $clientId = $this->clientService->create($payload, [
                'pan_document' => $request->file('pan_document'),
                'aadhaar_document' => $request->file('aadhaar_document'),
            ], Auth::id());
            Session::flash('success', 'Client created successfully. Capture portal credentials next.');
            redirect('/clients/credentials?id=' . $clientId);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/clients/create');
        }
    }

    public function show(Request $request): void
    {
        $clientId = (int) $request->input('id', 0);
        $client = $this->clients->findById($clientId);

        if ($client === null) {
            Response::abort(404, 'Client not found.');
        }

        $content = View::render(base_path('modules/Clients/views/show.php'), [
            'title' => 'Client Details',
            'activeMenu' => 'clients',
            'client' => $client,
            'contact' => $this->clients->primaryContact($clientId),
            'portalCredentials' => $this->clients->portalCredentials($clientId),
            'portalDefinitions' => ClientService::portalDefinitions(),
            'serviceOrders' => $this->clients->serviceOrders($clientId),
            'identityDocuments' => $this->clients->identityDocuments($clientId),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function edit(Request $request): void
    {
        $clientId = (int) $request->input('id', 0);
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            Response::abort(404, 'Client not found.');
        }

        $content = View::render(base_path('modules/Clients/views/form.php'), [
            'title' => 'Edit Client',
            'activeMenu' => 'clients',
            'mode' => 'edit',
            'publicRegistration' => false,
            'client' => $client,
            'contact' => $this->clients->primaryContact($clientId),
            'crmUsers' => $this->clients->crmUsers(),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function update(Request $request): void
    {
        $clientId = (int) $request->input('id', 0);
        $payload = $request->all();
        Session::flash('old', $payload);

        try {
            $this->clientService->update($clientId, $payload, [
                'pan_document' => $request->file('pan_document'),
                'aadhaar_document' => $request->file('aadhaar_document'),
            ], Auth::id());
            Session::flash('success', 'Client updated successfully.');
            redirect('/clients/show?id=' . $clientId);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/clients/edit?id=' . $clientId);
        }
    }

    public function archive(Request $request): void
    {
        $clientId = (int) $request->input('id', 0);

        try {
            $this->clientService->archive($clientId, (string) $request->input('archive_reason', ''));
            Session::flash('success', 'Client archived successfully.');
            redirect('/clients');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/clients/show?id=' . $clientId);
        }
    }

    public function credentials(Request $request): void
    {
        $clientId = (int) $request->input('id', 0);
        $client = $this->clients->findById($clientId);

        if ($client === null) {
            Response::abort(404, 'Client not found.');
        }

        $credentials = [];
        foreach ($this->clients->portalCredentials($clientId) as $credential) {
            $credentials[$credential['portal_code']] = $credential;
        }

        $content = View::render(base_path('modules/Clients/views/credentials.php'), [
            'title' => 'Portal Credentials',
            'activeMenu' => 'clients',
            'client' => $client,
            'portalDefinitions' => ClientService::portalDefinitions(),
            'credentials' => $credentials,
            'old' => Session::pullFlash('old_credentials', []),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function saveCredentials(Request $request): void
    {
        $clientId = (int) $request->input('id', 0);
        Session::flash('old_credentials', $request->all());

        try {
            $this->clientService->savePortalCredentials($clientId, $request->all(), (int) Auth::id());
            Session::flash('success', 'Portal credentials saved successfully.');
            redirect('/clients/show?id=' . $clientId);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/clients/credentials?id=' . $clientId);
        }
    }

    public function publicCreate(): void
    {
        $content = View::render(base_path('modules/Clients/views/form.php'), [
            'title' => 'Client Registration',
            'activeMenu' => null,
            'mode' => 'public_register',
            'publicRegistration' => true,
            'client' => null,
            'contact' => null,
            'crmUsers' => [],
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ], 'auth');

        Response::html($content);
    }

    public function publicStore(Request $request): void
    {
        $payload = $request->all();
        Session::flash('old', $payload);

        try {
            $result = $this->clientService->registerPortalClient($payload, [
                'pan_document' => $request->file('pan_document'),
                'aadhaar_document' => $request->file('aadhaar_document'),
            ]);
            Session::flash('success', 'Portal account created successfully. Your username is ' . $result['username'] . '. Please sign in using the password you set.');
            redirect('/login?audience=portal');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/register-client');
        }
    }
}
