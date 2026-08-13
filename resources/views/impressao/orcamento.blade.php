<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Orçamento {{ $orcamento->numero_orcamento }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .empresa { font-size: 14px; font-weight: bold; }
        .info-box { border: 1px solid #ccc; padding: 10px; margin-top: 15px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total-box { margin-top: 15px; text-align: right; font-size: 14px; font-weight: bold; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 15px; cursor: pointer;">🖨️ Imprimir / Salvar PDF</button>
    </div>

    <div class="header">
        <div>
            <div class="empresa">{{ $empresa->nome_fantasia ?? $empresa->razao_social }}</div>
            <div>CNPJ/CPF: {{ $empresa->cpf_cnpj }}</div>
        </div>
        <div style="text-align: right;">
            <h2>ORÇAMENTO</h2>
            <div><strong>Nº:</strong> {{ $orcamento->numero_orcamento }}</div>
            <div><strong>Data:</strong> {{ date('d/m/Y H:i', strtotime($orcamento->data_emissao)) }}</div>
            <div><strong>Validade:</strong> {{ date('d/m/Y', strtotime($orcamento->data_validade)) }}</div>
        </div>
    </div>

    <div class="info-box">
        <strong>CLIENTE:</strong> {{ $orcamento->cliente->nome_razao }}<br>
        <strong>CPF/CNPJ:</strong> {{ $orcamento->cliente->cpf_cnpj }} | <strong>Telefone:</strong> {{ $orcamento->cliente->telefone ?? 'N/I' }}<br>
        <strong>Email:</strong> {{ $orcamento->cliente->email ?? 'N/I' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Item / Descrição</th>
                <th style="width: 80px; text-align: center;">Qtd</th>
                <th style="width: 100px; text-align: right;">Unitário</th>
                <th style="width: 100px; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orcamento->itens as $item)
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
        <div>Subtotal: R$ {{ number_format($orcamento->valor_subtotal, 2, ',', '.') }}</div>
        @if($orcamento->valor_desconto > 0)
        <div style="color: red;">Desconto: - R$ {{ number_format($orcamento->valor_desconto, 2, ',', '.') }}</div>
        @endif
        <div style="font-size: 16px; margin-top: 5px;">TOTAL: R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</div>
    </div>

    @if($orcamento->observacoes)
    <div class="info-box" style="margin-top: 20px;">
        <strong>Observações:</strong><br>
        {{ $orcamento->observacoes }}
    </div>
    @endif
</body>
</html>