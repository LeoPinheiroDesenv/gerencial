<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recibo Térmico</title>
    <style>
        /* Define o tamanho da página: 80mm de largura x 200mm de altura */
        @page {
            size: 80mm 200mm;
            margin: 0;
        }

        /* Remove margens do body para ocupar toda a área */
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            font-size: 13px;
            color: #333;
        }

        /* Contêiner principal para controlar o espaçamento */
        .container {
            padding: 5px 8px; 
        }

        /* Título principal */
        .titulo {
            text-align: center;
            margin-bottom: 5px;
        }
        .titulo h2 {
            margin: 0;
            font-size: 16px;
        }

        /* Informações básicas da empresa */
        .info-basica {
            text-align: center;
            margin-bottom: 10px;
        }
        .info-basica p {
            margin: 2px 0;
        }

        /* Linha pontilhada para separar seções */
        .linha {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        /* Cabeçalho das seções (ex.: "DADOS DO PAGADOR") */
        .secao-titulo {
            font-weight: bold;
            text-transform: uppercase;
            margin: 6px 0 4px 0;
            text-align: center;
        }

        /* Blocos de dados */
        .dados {
            margin-bottom: 8px;
        }
        .dados p {
            margin: 2px 0;
            line-height: 1.2em;
        }
        .dados strong {
            display: inline-block;
            width: 70px; /* Ajuste se precisar de mais/menos espaço para rótulos */
        }

        /* Texto justificado, se desejar */
        .texto-justificado {
            text-align: justify;
        }

        /* Assinatura */
        .assinatura {
            text-align: center;
            margin-top: 20px;
        }
        .assinatura .linha-ass {
            border-top: 1px solid #000;
            width: 100px;
            margin: 0 auto;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Informações da Empresa (cabeçalho) -->
    <div class="info-basica">
    <p><strong>{{ $empresa->nome }}</strong></p>
        <p>CNPJ: {{ $empresa->cnpj }}</p>
        <p>{{ $empresa->rua }}, {{ $empresa->numero }}</p>
        <p>{{ $empresa->bairro }} - {{ $empresa->cidade }}/{{ $empresa->uf }}</p>
        <p>Tel: {{ $empresa->telefone }}</p>
    </div>

        <!-- Título do Recibo -->
        <div class="titulo">
        <h2>Nº Recibo: <strong>{{ $numeroRecibo }}</strong></h2>
    </div>

    <div class="linha"></div>

    <!-- Seção: Dados do Pagador -->
    <div class="secao-titulo">Dados do Pagador</div>
    <div class="dados">
        <p><strong>Cliente:</strong> {{ $recibo->cliente ?? 'Não informado' }}</p>
        <p><strong>Doc.:</strong> {{ $recibo->documento ?? 'Não informado' }}</p>
        <p><strong>Tel.:</strong> {{ $recibo->telefone ?? 'Não informado' }}</p>
        <p><strong>End.:</strong> {{ $recibo->endereco ?? 'Não informado' }}</p>
    </div>

    <div class="linha"></div>

    <!-- Seção: Dados do Recibo -->
    <div class="secao-titulo">Dados do Recibo</div>
    <div class="dados">
        <p><strong>Data:</strong>
            @if(isset($recibo->data_pagamento))
                {{ \Carbon\Carbon::parse($recibo->data_pagamento)->format('d/m/Y') }}
            @endif
        </p>
        <p><strong>Valor:</strong> R$ {{ number_format($recibo->valor_pago ?? 0, 2, ',', '.') }}</p>
        <p><strong>Forma:</strong> {{ $recibo->forma_pagamento ?? 'Não informado' }}</p>
        <p><strong>Ref.:</strong> {{ $recibo->referencia ?? 'Não informado' }}</p>
        @if(!empty($recibo->observacao))
            <p><strong>Obs.:</strong> {{ $recibo->observacao }}</p>
        @endif
    </div>

    <div class="linha"></div>

    <!-- Texto de quitação (opcional) -->
    <p class="texto-justificado">
        Recebi do(a) Sr(a). <strong>{{ $recibo->cliente ?? 'Nome não informado' }}</strong> 
        a quantia de R$ (<strong>{{ number_format($recibo->valor_pago ?? 0, 2, ',', '.') }}</strong>) 
        (<strong>{{ $recibo->valor_extenso ?? 'valor por extenso' }}</strong>),
        dando-lhe por este recibo a devida quitação.
    </p>
    <p>
        Local e Data: <strong>{{ $empresa->cidade ?? '' }}</strong>, 
        <strong>{{ now()->format('d') }}</strong>/
        <strong>{{ now()->format('m') }}</strong>/
        <strong>{{ now()->format('Y') }}</strong>
    </p>

    <!-- Assinatura -->
    <p style="text-align: center; margin-top: 40px;">
        ____________________________________ <br>
        Assinatura
    </p>


</div>
</body>
</html>
