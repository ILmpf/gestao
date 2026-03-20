<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
    .page { padding: 40px; }

    /* Header */
    .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
    .logo { max-height: 100px; max-width: 280px; }
    .company-info { text-align: right; line-height: 1.6; }
    .company-name { font-size: 14px; font-weight: bold; }

    /* Title */
    .doc-title { font-size: 20px; font-weight: bold; margin-bottom: 24px; color: #111; }
    .doc-meta { display: flex; gap: 40px; margin-bottom: 24px; }
    .doc-meta div { line-height: 1.8; }
    .doc-meta .label { font-weight: bold; font-size: 10px; text-transform: uppercase; color: #555; }

    /* Customer */
    .client-box { border: 1px solid #e0e0e0; border-radius: 4px; padding: 12px 16px; margin-bottom: 24px; background: #fafafa; }
    .client-box .label { font-weight: bold; font-size: 10px; text-transform: uppercase; color: #555; margin-bottom: 4px; }

    /* Table of content */
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    thead tr { background: #1a1a1a; color: #fff; }
    thead th { padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
    .text-right { text-align: right; }
    tbody tr:nth-child(even) { background: #f7f7f7; }
    tbody td { padding: 8px 10px; border-bottom: 1px solid #e8e8e8; }

    /* Totals */
    .totals { width: 260px; margin-left: auto; }
    .totals table { margin-bottom: 0; }
    .totals td { padding: 5px 10px; }
    .totals .total-row { font-weight: bold; font-size: 13px; border-top: 2px solid #1a1a1a; }

    /* Footer */
    .footer { margin-top: 40px; font-size: 10px; color: #888; text-align: center; border-top: 1px solid #e0e0e0; padding-top: 12px; }

    .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; }
    .badge-apresentada { background: #dbeafe; color: #1e40af; }
    .badge-fechada     { background: #dcfce7; color: #166534; }
    .badge-rejeitada   { background: #fee2e2; color: #991b1b; }
</style>
</head>
<body>
<div class="page">

    <!-- Header -->
    <div class="header">
        <div>
            @if($empresa?->logo)
                <img class="logo" src="{{ Storage::url($empresa->logo) }}" alt="Logo">
            @else
                <span class="company-name">{{ $empresa?->nome ?? config('app.name') }}</span>
            @endif
        </div>
        <div class="company-info">
            <div class="company-name">{{ $empresa?->nome ?? config('app.name') }}</div>
            @if($empresa?->morada)<div>{{ $empresa->morada }}</div>@endif
            @if($empresa?->codigo_postal || $empresa?->cidade)
                <div>{{ implode(' ', array_filter([$empresa->codigo_postal, $empresa->cidade])) }}</div>
            @endif
            @if($empresa?->nif)<div>NIF: {{ $empresa->nif }}</div>@endif
        </div>
    </div>

    <!-- Doc Title + Meta -->
    <div class="doc-title">Proposta {{ $proposta->numero }}</div>

    <div class="doc-meta">
        <div>
            <div class="label">Data</div>
            <div>{{ $proposta->data_proposta?->format('d/m/Y') ?? '—' }}</div>
        </div>
        <div>
            <div class="label">Validade</div>
            <div>{{ $proposta->validade?->format('d/m/Y') ?? '—' }}</div>
        </div>
        <div>
            <div class="label">Estado</div>
            <div>
                <span class="badge badge-{{ $proposta->estado }}">
                    {{ ucfirst($proposta->estado) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Customer -->
    <div class="client-box">
        <div class="label">Cliente</div>
        <div>{{ $proposta->entidade?->nome }}</div>
        @if($proposta->entidade?->morada)<div>{{ $proposta->entidade->morada }}</div>@endif
        @if($proposta->entidade?->city || $proposta->entidade?->codigo_postal)
            <div>{{ implode(' ', array_filter([$proposta->entidade->codigo_postal, $proposta->entidade->city])) }}</div>
        @endif
        @if($proposta->entidade?->nif)<div>NIF: {{ $proposta->entidade->nif }}</div>@endif
    </div>

    <!-- Lines -->
    <table>
        <thead>
            <tr>
                <th>Ref.</th>
                <th>Designação</th>
                <th class="text-right">Qtd.</th>
                <th class="text-right">Preço Unit.</th>
                <th class="text-right">IVA</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proposta->linhas as $linha)
            <tr>
                <td>{{ $linha->artigo?->referencia }}</td>
                <td>{{ $linha->artigo?->nome }}</td>
                <td class="text-right">{{ number_format((float)$linha->quantidade, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format((float)$linha->preco_unitario, 2, ',', '.') }} €</td>
                <td class="text-right">{{ $linha->taxaIva ? $linha->taxaIva->taxa . '%' : '—' }}</td>
                <td class="text-right">{{ number_format((float)$linha->subtotal, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <table>
            @php
                $subtotalSemIva = $proposta->linhas->sum('subtotal');
                $totalIva = $proposta->linhas->sum(fn($l) =>
                    (float)$l->subtotal * ((float)($l->taxaIva?->taxa ?? 0) / 100)
                );
                $total = $subtotalSemIva + $totalIva;
            @endphp
            <tr>
                <td>Subtotal (s/ IVA)</td>
                <td class="text-right">{{ number_format($subtotalSemIva, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td>IVA</td>
                <td class="text-right">{{ number_format($totalIva, 2, ',', '.') }} €</td>
            </tr>
            <tr class="total-row">
                <td>Total</td>
                <td class="text-right">{{ number_format($total, 2, ',', '.') }} €</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        {{ $empresa?->nome ?? config('app.name') }} —
        Documento gerado em {{ now()->format('d/m/Y H:i') }}
    </div>

</div>
</body>
</html>