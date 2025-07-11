<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Recebimento</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
            margin: 20px;
            color: #333;
        }
        .empresa-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .empresa-info h1 {
            margin: 0;
            font-size: 22px;
        }
        .empresa-info p {
            margin: 2px 0;
        }
        .titulo {
            text-align: center;
            margin-bottom: 30px;
        }
        .titulo h2 {
            margin: 0;
            font-size: 20px;
        }
        .subtitulo {
            font-size: 16px;
            margin: 10px 0;
            text-decoration: underline;
        }
        .linha-info {
            margin-bottom: 10px;
        }
        .linha-info strong {
            width: 150px;
            display: inline-block;
        }
        .secao {
            margin-bottom: 20px;
        }
        /* Tabelas com bordas */
        table {
            width: 100%;
            border-collapse: collapse; /* Remove espaços entre células */
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #000; /* Borda de 1px */
            padding: 5px;           /* Espaçamento interno */
            vertical-align: top;    /* Alinhamento vertical */
        }
        th {
            text-align: center;     
            background-color: #f5f5f5; /* Fundo do cabeçalho */
        }

        /* Assinatura */
        .assinatura {
            margin-top: 40px;
            text-align: center;
        }
        .assinatura .linha {
            margin-top: 50px;
            border-top: 1px solid #000;
            width: 250px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <!-- Dados da Empresa (Emitente) -->
    <div class="empresa-info">
        <h1>{{ $empresa->nome }}</h1>
        <p>CNPJ: {{ $empresa->cnpj }}</p>
        <p>Endereço: {{ $empresa->rua }}, {{ $empresa->numero }} - {{ $empresa->bairro }}, {{ $empresa->cidade }}, {{ $empresa->uf }}</p>
        <p>Telefone: {{ $empresa->telefone }}</p>
    </div>
    
    <!-- Título do Recibo -->
    <div class="titulo">
        <h2>Nº Recibo: <strong>{{ $numeroRecibo }}</strong></h2>
    </div>
    
<!-- Tabela: Dados do Pagador -->
<table>
    <tr>
        <th colspan="4">DADOS DO PAGADOR</th>
    </tr>

    <!-- Linha do Cliente, sem divisões internas -->
    <tr>
        <td colspan="4" 
            style="
                border-left: 1px solid #000; 
                border-right: 1px solid #000; 
                border-top: 1px solid #000; 
                border-bottom: 1px solid #000;
            "
        >
            <strong>Cliente:</strong> {{ $recibo->cliente ?? 'Não informado' }}
        </td>
    </tr>

    <!-- Documento (2 colunas) e Telefone (2 colunas) na mesma linha -->
    <tr>
        <!-- Documento -->
        <td colspan="2" style="
            width: 50%; 
            border: 1px solid #000; 
            border-right: none;
        ">
            <strong>Documento:</strong> {{ $recibo->documento ?? 'Não informado' }}
        </td>
        
        <!-- Telefone -->
        <td colspan="2" style="
            width: 50%; 
            border: 1px solid #000; 
            border-left: 1px solid #000;
        ">
            <strong>Telefone:</strong> {{ $recibo->telefone ?? 'Não informado' }}
        </td>
    </tr>

    <!-- Endereço, sem divisões internas -->
    <tr>
        <td colspan="4" 
            style="
                border-left: 1px solid #000; 
                border-right: 1px solid #000; 
                border-top: 1px solid #000; 
                border-bottom: 1px solid #000;
            "
        >
            <strong>Endereço:</strong> {{ $recibo->endereco ?? 'Não informado' }}
        </td>
    </tr>
</table>

<!-- Tabela: Dados do Recibo -->
<table>
    <tr>
        <th colspan="4">DADOS DO RECIBO</th>
    </tr>

    <!-- 1ª linha: Data de Pagamento | Referência -->
    <tr>
        <td colspan="2" style="width: 50%; border: 1px solid #000; border-right: none;">
            <strong>Data de Pagamento:</strong>
            @if(isset($recibo->data_pagamento))
                {{ \Carbon\Carbon::parse($recibo->data_pagamento)->format('d/m/Y') }}
            @endif
        </td>
        <td colspan="2" style="width: 50%; border: 1px solid #000; border-left: 1px solid #000;">
            <strong>Referência:</strong>
            {{ $recibo->referencia ?? 'Não informado' }}
        </td>
    </tr>

    <!-- 2ª linha: Forma de Pagamento | Valor Pago -->
    <tr>
        <td colspan="2" style="width: 50%; border: 1px solid #000; border-right: none;">
            <strong>Forma de Pagamento:</strong>
            {{ $recibo->forma_pagamento ?? 'Não informado' }}
        </td>
        <td colspan="2" style="width: 50%; border: 1px solid #000; border-left: 1px solid #000;">
            <strong>Valor Pago:</strong>
            R$ {{ number_format($recibo->valor_pago ?? 0, 2, ',', '.') }}
        </td>
    </tr>

    <!-- 3ª linha: Observação (toda a largura) -->
    <tr>
        <td colspan="4" style="border: 1px solid #000;">
            @if(!empty($recibo->observacao))
                <strong>Observação:</strong>
                {{ $recibo->observacao }}
            @endif
        </td>
    </tr>
</table>

<!-- Após as tabelas ou onde desejar exibir o texto -->
<div style="margin-top: 30px; line-height: 1.6;">
    <p>
        Recebi do Sr(a) <strong>{{ $recibo->cliente ?? 'Nome não informado' }}</strong>,
        residente e domiciliado(a) na rua 
        <strong>{{ $recibo->endereco ?? 'Endereço não informado' }}</strong>, 
        a quantia de R$ (<strong>{{ number_format($recibo->valor_pago ?? 0, 2, ',', '.') }}</strong>)
        (<strong>{{ $recibo->valor_extenso ?? 'valor por extenso' }}</strong>),
        dando-lhe por este recibo a devida quitação.
    </p>
    <p style="margin-top: 20px;">
        Local e Data: 
        <strong>{{ $empresa->cidade ?? '' }}</strong>, 
        <strong>{{ now()->format('d') }}</strong> do 
        <strong>{{ now()->format('m') }}</strong> de 
        <strong>{{ now()->format('Y') }}</strong>
    </p>
    <p style="text-align: center; margin-top: 40px;">
        ____________________________________ <br>
        Assinatura
    </p>
</div>

</body>
</html>