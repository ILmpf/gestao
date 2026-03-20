<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Comprovativo de Pagamento</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>Estimado(a) {{ $fornecedor?->nome ?? 'Fornecedor' }},</p>

    <p>Enviamos em anexo o comprovativo de pagamento da fatura <strong>{{ $fatura->numero }}</strong>.</p>

    <p>Qualquer questão, entre em contacto connosco.</p>

    <p>Cumprimentos,</p>

    @php $empresa = \App\Models\Empresa::first(); @endphp
    @if ($empresa?->caminho_logotipo)
        <img
            src="{{ Storage::url($empresa->caminho_logotipo) }}"
            alt="{{ $empresa->nome }}"
            style="max-height: 60px; margin-top: 12px;"
        >
    @else
        <strong>{{ $empresa?->nome ?? config('app.name') }}</strong>
    @endif
</body>
</html>