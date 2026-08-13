<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ordem de Serviço {{ $os->numero_os }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .empresa { font-size: 14px; font-weight: bold; }
        .info-box { border: 1px solid #ccc; padding: 10px; margin-top: 15px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total-box { margin-top: 15px; text-align: right; font-size: 14px; font-weight: bold; }
        .assinatura { margin-top: 50px; display: flex; justify-content: space-around; text-align: center; }
        .linha-assinatura { border-top: 1px solid #000; width: 200px; padding-top: 5px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 15px; cursor: pointer;">🖨️ Imprimir OS / PDF</button>
    </div>

    <div class="header">
        <div>
            <div class="empresa">{{ $empresa->nome_fantasia ?? $empresa->razao_social }}</div>
            <div>CNPJ/CPF: {{ $empresa->cpf_cnpj }}</div>
        </div>
        <div style="text-align: right;">
            <h2>ORDEM DE SERVIÇO</h2>
            <div><strong>Nº:</strong> {{ $os->numero_os }}</div>
            <div><strong>Status:</strong> {{ $os->status }}</div>
            <div><strong>Abertura:</strong> {{ date('d/m/Y H:i', strtotime($os->data_abertura)) }}</div>
        </div>
    </div>

    <div class="info-box">
        <strong>CLIENTE:</strong> {{ $os->cliente->nome_razao }}<br>
        <strong>CPF/CNPJ:</strong> {{ $os->cliente->cpf_cnpj }} | <strong>Telefone:</strong> {{ $os->cliente->telefone ?? 'N/I' }}
    </div>

    @if($os->defeito_relatado)
    <div class="info-box">
        <strong>Defeito Relatado / Solicitação:</strong><br>
        {{ $os->defeito_relatado }}
    </div>
    @endif

    @if($os->laudo_tecnico)
    <div class="info-box">
        <strong>Laudo Técnico / Parecer:</strong><br>
        {{ $os->laudo_tecnico }}
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Serviço / Peça Utilizada</th>
                <th style="width: 80px; text-align: center;">Qtd</th>
                <th style="width: 100px; text-align: right;">Unitário</th>
                <th style="width: 100px; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($os->itens as $item)
            <tr>
                <td>{{ $item->descricao_item }}</td>
                <td style="text-align: center;">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                <td style="text-align: right;">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                <td style="text-align: right;">R$ {{ number_format($item->valor_subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <div>Serviços: R$ {{ number_format($os->valor_servicos, 2, ',', '.') }} | Produtos: R$ {{ number_format($os->valor_produtos, 2, ',', '.') }}</div>
        @if($os->valor_desconto > 0)
        <div style="color: red;">Desconto: - R$ {{ number_format($os->valor_desconto, 2, ',', '.') }}</div>
        @endif
        <div style="font-size: 16px; margin-top: 5px;">TOTAL: R$ {{ number_format($os->valor_total, 2, ',', '.') }}</div>
    </div>

    <div class="assinatura">
        <div class="linha-assinatura">Técnico / Responsável</div>
        <div class="linha-assinatura">Assinatura do Cliente</div>
    </div>
</body>
</html>