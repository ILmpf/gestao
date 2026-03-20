<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoutePermission
{
    /**
     * Maps route name prefix → permission menu key.
     * Longest prefixes must come first to avoid partial matches.
     *
     * @var array<string, string>
     */
    private const MENU_MAP = [
        'encomendas.clientes'                   => 'encomendas-clientes',
        'encomendas.fornecedores'               => 'encomendas-fornecedores',
        'financeiro.faturas-fornecedores'       => 'faturas-fornecedores',
        'financeiro.contas-bancarias'           => 'contas-bancarias',
        'financeiro.conta-corrente-clientes'    => 'conta-corrente',
        'financeiro.notas-credito-fornecedores' => 'notas-credito',
        'gestao-acessos.utilizadores'           => 'utilizadores',
        'gestao-acessos.permissoes'             => 'permissoes',
        'configuracoes.paises'                  => 'config-paises',
        'configuracoes.funcoes-contacto'        => 'config-funcoes-contacto',
        'configuracoes.tipos-calendario'        => 'config-tipos-calendario',
        'configuracoes.accoes-calendario'       => 'config-accoes-calendario',
        'configuracoes.artigos'                 => 'config-artigos',
        'configuracoes.taxas-iva'               => 'config-taxas-iva',
        'configuracoes.logs'                    => 'config-logs',
        'configuracoes.empresa'                 => 'config-empresa',
        'clientes'                              => 'clientes',
        'fornecedores'                          => 'fornecedores',
        'contactos'                             => 'contactos',
        'propostas'                             => 'propostas',
        'calendario'                            => 'calendario',
        'arquivo-digital'                       => 'arquivo-digital',
    ];

    /**
     * Maps route name suffix → Spatie permission action.
     *
     * @var array<string, string>
     */
    private const ACTION_MAP = [
        'index'                  => 'ver',
        'show'                   => 'ver',
        'pdf'                    => 'ver',
        'fatura'                 => 'ver',
        'nota-credito'           => 'ver',
        'create'                 => 'criar',
        'store'                  => 'criar',
        'edit'                   => 'editar',
        'update'                 => 'editar',
        'converter'              => 'editar',
        'converter-fornecedor'   => 'editar',
        'destroy'                => 'eliminar',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Administrador bypasses all checks (also covered by Gate::before)
        if ($user->hasRole('Administrador')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (! $routeName) {
            return $next($request);
        }

        // Special case: entidades.* routes are shared between clientes and fornecedores.
        // Allow if the user can perform the equivalent action on either type.
        if (str_starts_with($routeName, 'entidades.')) {
            $suffix = substr($routeName, strlen('entidades.'));
            $action = self::ACTION_MAP[$suffix] ?? null;

            if ($action && ! $user->can("clientes.{$action}") && ! $user->can("fornecedores.{$action}")) {
                abort(403, 'Sem permissão para aceder a esta secção.');
            }

            return $next($request);
        }

        // Look up the menu key and action for the current route
        $menuKey = null;
        $action  = null;

        foreach (self::MENU_MAP as $prefix => $key) {
            if (str_starts_with($routeName, $prefix . '.')) {
                $menuKey = $key;
                $suffix  = substr($routeName, strlen($prefix) + 1);
                $action  = self::ACTION_MAP[$suffix] ?? null;
                break;
            }
        }

        // Route not in the permission map (e.g. dashboard, vies, files) — allow through
        if ($menuKey === null || $action === null) {
            return $next($request);
        }

        if (! $user->can("{$menuKey}.{$action}")) {
            abort(403, 'Sem permissão para aceder a esta secção.');
        }

        return $next($request);
    }
}
