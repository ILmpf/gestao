<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gestao;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissaoRequest;
use App\Http\Requests\UpdatePermissaoRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissaoController extends Controller
{
    private const MENUS = [
        'Clientes'                   => 'clientes',
        'Fornecedores'               => 'fornecedores',
        'Contactos'                  => 'contactos',
        'Propostas'                  => 'propostas',
        'Encomendas de Clientes'     => 'encomendas-clientes',
        'Encomendas de Fornecedores' => 'encomendas-fornecedores',
        'Faturas Clientes'           => 'faturas-clientes',
        'Faturas Fornecedores'       => 'faturas-fornecedores',
        'Notas de Crédito'           => 'notas-credito',
        'Contas Bancárias'           => 'contas-bancarias',
        'Conta Corrente'             => 'conta-corrente',
        'Calendário'                 => 'calendario',
        'Arquivo Digital'            => 'arquivo-digital',
        'Utilizadores'               => 'utilizadores',
        'Permissões'                 => 'permissoes',
        'Config - Países'            => 'config-paises',
        'Config - Funções Contacto'  => 'config-funcoes-contacto',
        'Config - Tipos Calendário'  => 'config-tipos-calendario',
        'Config - Acções Calendário' => 'config-accoes-calendario',
        'Config - Artigos'           => 'config-artigos',
        'Config - Taxas IVA'         => 'config-taxas-iva',
        'Config - Logs'              => 'config-logs',
        'Config - Empresa'           => 'config-empresa',
    ];

    private const ACTIONS = ['ver', 'criar', 'editar', 'eliminar'];

    /** @return list<array{label: string, key: string}> */
    private function menuOptions(): array
    {
        $result = [];
        foreach (self::MENUS as $label => $key) {
            $result[] = ['label' => $label, 'key' => $key];
        }

        return $result;
    }

    public function index(): Response
    {
        $permissoes = Role::withCount(['users', 'permissions'])
            ->orderBy('name')
            ->get()
            ->map(fn (Role $r) => [
                'id'                => $r->id,
                'name'              => $r->name,
                'users_count'       => $r->users_count,
                'permissions_count' => $r->permissions_count,
            ]);

        return Inertia::render('gestao-acessos/permissoes/Index', [
            'permissoes' => $permissoes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('gestao-acessos/permissoes/Form', [
            'menus'   => $this->menuOptions(),
            'actions' => self::ACTIONS,
        ]);
    }

    public function store(StorePermissaoRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
        $this->syncPermissions($role, $request->validated('permissions') ?? []);
        $this->syncPermissions($role, $request->validated('permissions') ?? []);

        return redirect()->route('gestao-acessos.permissoes.index')
            ->with('success', 'Grupo de permissões criado com sucesso.');
    }

    public function edit(int $permissao): Response
    {
        $role = Role::with('permissions')->findOrFail($permissao);

        return Inertia::render('gestao-acessos/permissoes/Form', [
            'permissao' => [
                'id'          => $role->id,
                'name'        => $role->name,
                'permissions' => $role->permissions->pluck('name')->values(),
            ],
            'menus'   => $this->menuOptions(),
            'actions' => self::ACTIONS,
        ]);
    }

    public function update(UpdatePermissaoRequest $request, int $permissao): RedirectResponse
    {
        $role = Role::findOrFail($permissao);
        $role->update(['name' => $request->validated('name')]);
        $this->syncPermissions($role, $request->validated('permissions') ?? []);

        return redirect()->route('gestao-acessos.permissoes.index')
            ->with('success', 'Grupo de permissões atualizado com sucesso.');
    }

    public function destroy(int $permissao): RedirectResponse
    {
        $role = Role::findOrFail($permissao);

        if ($role->users()->exists()) {
            return redirect()->route('gestao-acessos.permissoes.index')
                ->with('error', 'Não é possível eliminar um grupo com utilizadores associados.');
        }

        $role->delete();

        return redirect()->route('gestao-acessos.permissoes.index')
            ->with('success', 'Grupo de permissões eliminado com sucesso.');
    }

    /** @param list<string> $permissionNames */
    private function syncPermissions(Role $role, array $permissionNames): void
    {
        $permissions = collect($permissionNames)->map(
            fn (string $name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'])
        );

        $role->syncPermissions($permissions);
    }
}
