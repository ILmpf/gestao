<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gestao;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUtilizadorRequest;
use App\Http\Requests\UpdateUtilizadorRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UtilizadorController extends Controller
{
    /** @return array<string, mixed> */
    private function mapUser(User $user): array
    {
        return [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'telemovel' => $user->telemovel,
            'role'      => $user->roles->first()?->name,
            'estado'    => $user->estado,
        ];
    }

    public function index(): Response
    {
        $utilizadores = User::with('roles')
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => $this->mapUser($u));

        return Inertia::render('gestao-acessos/utilizadores/Index', [
            'utilizadores' => $utilizadores,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('gestao-acessos/utilizadores/Form', [
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreUtilizadorRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = $data['role'] ?? null;
        unset($data['role']);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        if ($role) {
            $user->syncRoles([$role]);
        }

        return redirect()->route('gestao-acessos.utilizadores.index')
            ->with('success', 'Utilizador criado com sucesso.');
    }

    public function edit(User $utilizador): Response
    {
        return Inertia::render('gestao-acessos/utilizadores/Form', [
            'utilizador' => $this->mapUser($utilizador->load('roles')),
            'roles'      => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateUtilizadorRequest $request, User $utilizador): RedirectResponse
    {
        $data = $request->validated();
        $role = $data['role'] ?? null;
        unset($data['role']);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $utilizador->update($data);
        $utilizador->syncRoles($role ? [$role] : []);

        return redirect()->route('gestao-acessos.utilizadores.index')
            ->with('success', 'Utilizador atualizado com sucesso.');
    }

    public function destroy(User $utilizador): RedirectResponse
    {
        if ($utilizador->is(Auth::user())) {
            return redirect()->route('gestao-acessos.utilizadores.index')
                ->with('error', 'Não pode eliminar a sua própria conta.');
        }

        $utilizador->delete();

        return redirect()->route('gestao-acessos.utilizadores.index')
            ->with('success', 'Utilizador eliminado com sucesso.');
    }
}
