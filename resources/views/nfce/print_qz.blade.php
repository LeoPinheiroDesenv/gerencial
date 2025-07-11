<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cupom Direto - ESC/POS</title>
    <style>
        /* Estes estilos são apenas para visualização na tela – a impressão é feita via comandos ESC/POS */
        body {
            font-family: <?= isset($fontePadrao) ? $fontePadrao : 'Times New Roman'; ?>, serif;
            font-size: 10pt;
            margin: 20px;
        }
        .container {
            margin: 0 auto;
            padding: 5px;
            width: 227px;
            border: 1px dashed #ccc;
        }
    </style>
</head>
<body>
<?php
if (! function_exists('formatCpf')) {
    function formatCpf($cpf)
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        return substr($cpf, 0, 3) . '.'
             . substr($cpf, 3, 3) . '.'
             . substr($cpf, 6, 3) . '-'
             . substr($cpf, 9, 2);
    }
}
?>    
<?php
// no topo do print_qz.blade.php, antes de qualquer uso de $config:
if (! function_exists('formatCnpj')) {
    function formatCnpj($cnpj)
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        return substr($cnpj, 0, 2) . '.' .
               substr($cnpj, 2, 3) . '.' .
               substr($cnpj, 5, 3) . '/' .
               substr($cnpj, 8, 4) . '-' .
               substr($cnpj, 12, 2);
    }
}
if (! function_exists('formatIe')) {
    function formatIe($ie)
    {
        $ie = preg_replace('/\D/', '', $ie);
        // ajuste a máscara abaixo conforme a sua IE; ex: 9 dígitos com pontos a cada 3
        return substr($ie, 0, 3) . '.' .
               substr($ie, 3, 3) . '.' .
               substr($ie, 6, 3);
    }
}
?>    
<?php
if (! function_exists('removeAccents')) {
    function removeAccents($string) {
        $unwantedArray = [
            'Á'=>'A','À'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A','Å'=>'A','á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a',
            'É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
            'Ó'=>'O','Ò'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O','Ø'=>'O','ó'=>'o','ò'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','ø'=>'o',
            'Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
            'Ç'=>'C','ç'=>'c',
            'Ñ'=>'N','ñ'=>'n',
            'Ý'=>'Y','ý'=>'y','ÿ'=>'y'
        ];
        return strtr($string, $unwantedArray);
    }
}

    // subtotal já existente
    $subtotal = 0;
    foreach($venda->itens as $item) {
        $subtotal += $item->quantidade * $item->valor;
    }

    // === definição das variáveis do Resumo Geral ===
    $totalItens          = count($venda->itens);
    $totalValorItens     = 0;
    $totalDescontosItens = 0;
    $subtotalFinal       = 0;

    foreach($venda->itens as $item) {
        // acumula valor original * qtde
        $totalValorItens     += $item->valor_original * $item->quantidade;
        // acumula (original – final) * qtde
        $totalDescontosItens += ($item->valor_original - $item->valor) * $item->quantidade;
        // acumula subtotal final (já com descontos)
        $subtotalFinal       += $item->valor * $item->quantidade;
    }

    $descontoGeral   = $venda->desconto ?? 0;
    $acrescimoGeral   = $venda->acrescimo ?? 0;
    $valorTotalVenda = $venda->valor_total;
?>

<?php
use Carbon\Carbon;
use App\Models\FaturaFrenteCaixa;

// monta o array de pagamentos
if ($venda->tipo_pagamento !== '99') {
    // único pagamento
    $venc = null;
    if ($venda->tipo_pagamento === '06') {
        // vencimento = data da venda + 30 dias
        $venc = Carbon::parse($venda->created_at)
                      ->addDays(30)
                      ->format('d/m/Y');
    }
    $pagamentos = collect([(object)[
        'forma_pagamento' => $venda->tipo_pagamento,
        'valor'           => $venda->dinheiro_recebido ?? $venda->valor_total,
        'data_vencimento' => $venc,
    ]]);
} else {
    // múltiplos pagamentos
    $pagamentos = FaturaFrenteCaixa::where('venda_caixa_id', $venda->id)
        ->select('forma_pagamento','valor','data_vencimento')
        ->get()
        ->map(function($f){
            return (object)[
                'forma_pagamento'  => $f->forma_pagamento,
                'valor'            => $f->valor,
                'data_vencimento'  => $f->data_vencimento
                    ? Carbon::parse($f->data_vencimento)->format('d/m/Y')
                    : null,
            ];
        });
}

// recalcula o troco somando todos os pagamentos
$totalPago = $pagamentos->sum('valor');
$troco     = $totalPago - $venda->valor_total;
?>

<?php
    // URL de consulta
    $consultaUrl   = 'http://www.fazenda.pr.gov.br/nfce/consulta';
    // Agrupa a chave em blocos de 4 dígitos
    $formattedKey  = implode(' ', str_split($venda->chave, 4));
?>

  <!-- Conteúdo visual para exibição (opcional) -->
  <div class="container">
    <!-- Cabeçalho com dados da empresa -->
    <div class="header">
      <?php if(!empty($config->logo)): ?>
        <img src="<?= asset('logos/' . $config->logo) ?>" alt="Logo">
      <?php endif; ?>
      <div class="header-info">
        <?= removeAccents($config->razao_social); ?><br>
        @if(!empty($config->fantasia))
        <?= removeAccents($config->fantasia); ?><br>
        @endif
        CNPJ: <?= formatCnpj($config->cnpj); ?> – IE: <?= formatIe($config->ie); ?><br>
        <?= removeAccents($config->logradouro . ', ' . $config->numero . ', ' . $config->bairro); ?><br>
        Telefone: <?= $config->fone; ?>
      </div>
    </div>
    <hr class="dashed">
    <!-- Título do cupom -->
    <div class="header-secondary">
        <p style="text-align:center; margin:0; font-size:9pt;">
            Doc. Auxiliar da Nota Fiscal de Consumidor Eletronica
        </p>
        <p style="text-align:center; margin:0 0 5px; font-size:7pt;">
            Nao permite aproveitamento de credito de ICMS
        </p>
    </div>
    <hr class="dashed">
    <!-- Tabela de produtos para visualização -->
    <div class="products">
      <table style="width:100%; font-size:10pt;">
        <thead>
          <tr>
            <th style="text-align:left;">DESCRICAO</th>
            <th style="text-align:center;">QTD</th>
            <th style="text-align:right;">VALOR</th>
            <th style="text-align:right;">TOTAL</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($venda->itens as $item): ?>
            <tr>
              <td><?= mb_strimwidth($item->produto->nome, 0, 20, '...'); ?></td>
              <td style="text-align:center;"><?= number_format($item->quantidade, $config->casas_decimais_qtd); ?></td>
              <td style="text-align:right;"><?= number_format($item->valor_original, $config->casas_decimais, ',', '.'); ?></td>
              <td style="text-align:right;"><?= number_format($item->desconto, $config->casas_decimais, ',', '.'); ?></td>
              <td style="text-align:right;"><?= number_format($item->valor * $item->quantidade, $config->casas_decimais, ',', '.'); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <hr class="dashed">
    <div class="totals">
      <p style="text-align:right;"><strong>Total: R$ <?= number_format($venda->valor_total, $config->casas_decimais, ',', '.'); ?></strong></p>
      <p>Data: <?= \Carbon\Carbon::parse($venda->created_at)->format('d/m/Y H:i:s'); ?></p>
    </div>
    <?php if(!empty($venda->observacao)): ?>
      <div class="observacao">
        <strong>Observação:</strong> <?= $venda->observacao; ?>
      </div>
      <hr class="dashed">
    <?php endif; ?>
    <?php if($venda->cliente_id != null): ?>
      <div class="customer">
        <strong>Cliente:</strong> <?= removeAccents($venda->cliente->razao_social); ?><br>
        <strong>Telefone:</strong> <?= $venda->cliente->telefone . ' - ' . $venda->cliente->celular; ?><br>
        <strong>Endereço:</strong> <?= removeAccents($venda->cliente->rua . ', ' . $venda->cliente->numero . ' - ' . $venda->cliente->bairro . ' - ' . $venda->cliente->complemento . ' - ' . $venda->cliente->cidade->nome . ' (' . $venda->cliente->cidade->uf . ')'); ?>
      </div>
      <hr class="dashed">
      <div class="signature">
        _______________________________<br>
        Assinatura
      </div>
      <hr class="dashed">
    <?php endif; ?>
    <?php if(!empty($configCaixa->mensagem_padrao_cupom)): ?>
      <div class="footer">
        <?= removeAccents($configCaixa->mensagem_padrao_cupom); ?>
      </div>
    <?php endif; ?>
  </div>
<!-- Preview de Tributos -->
<div id="tributos-preview" style="text-align:center; margin:1rem 0; font-size:10pt;">
  <strong>Tributos totais Incidentes (Lei Federal 12.741/2012):</strong><br>
  R$ {{ $trib }}<br>
  {{-- só o trecho aproximado --}}
  {{ $approxLine }}<br>

  {{-- exibe todas as linhas de infCpl vindas do XML --}}
  @if(count($linesCpl))
    @foreach($linesCpl as $line)
      {{ $line }}<br>
    @endforeach
  @endif

  {{-- adiciona Série e Número da NF-e --}}
  <strong>Série:</strong> {{ $serieXml }}<br>
  <strong>Nota nº:</strong> {{ $nnfXml }}<br>
  <strong>Emissão:</strong> {{ $dhEmi }}<br>
  <strong>Protocolo:</strong> {{ $nProt }}<br>
  <strong>Autorização:</strong> {{ $dhRecb }}<br>
</div>
<div id="qr-preview" style="width:128px;height:128px;margin:1rem auto;"></div>
<script>const QR_DATA = @json($qrString);</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<!-- QZ Tray core e Image Utils, nessa ordem -->
<script src="/js/qz-tray.js"></script>
<script src="/js/qz-tray-image-utils.js"></script>
<script>
document.addEventListener("DOMContentLoaded", async function() {
  // 1) Gera o preview do QR no HTML (tamanho maior)
  new QRCode(document.getElementById("qr-preview"), {
    text: QR_DATA,
    width: 256,
    height: 256,
    correctLevel: QRCode.CorrectLevel.H
  });

  // 2) Configurações de segurança do QZ Tray
  qz.security.setCertificatePromise((res, rej) => {
    fetch('/assets/qz-certs/digital-certificate.txt', { cache: 'no-store' })
      .then(r => r.ok ? r.text() : Promise.reject(r.statusText))
      .then(cert => res(cert))
      .catch(err => rej(err));
  });
  qz.security.setSignatureAlgorithm('SHA512');
  qz.security.setSignaturePromise(toSign => (resolve, reject) => {
    fetch('/sign-message.php?request=' + encodeURIComponent(toSign), { cache: 'no-store' })
      .then(r => r.ok ? r.text() : Promise.reject(r.statusText))
      .then(sig => resolve(sig))
      .catch(err => reject(err));
  });

  // 3) Conecta **uma vez** e prepara printer/config
  await qz.websocket.connect();
  const printer = await qz.printers.getDefault();
  const cfg     = qz.configs.create(printer, {
    encoding: 'ISO-8859-1',
    forceRaw: true,
    copies:   1,
    dialog:   false
  });

  // 4) Constantes ESC/POS
  const ESC = "\x1B", GS = "\x1D";

        // Seleciona a code page – tente "\x00" ou "\x01" conforme a impressora
        var selectCodePage = ESC + "t" + "\x00";

        // Monta os comandos ESC/POS para o cupom
        var cmds = "";
        cmds += selectCodePage;
        // Cabeçalho com dados da empresa (convertidos para CP850)
        cmds  += ESC + "@"                           // Inicializa a impressora
              + ESC + "3" + "\x08"
              + ESC + "M" + "\x01"
              + ESC + "a" + "\x01"                    // Centraliza
              + "<?= iconv('UTF-8', 'CP850//TRANSLIT', removeAccents($config->razao_social)); ?>\n"
        @if(!empty($config->fantasia))
        cmds  += "<?= iconv('UTF-8','CP850//TRANSLIT', removeAccents($config->fantasia)); ?>\n"
        @endif
        cmds += "CNPJ: <?= formatCnpj($config->cnpj); ?>  IE: <?= formatIe($config->ie); ?>\n"
        cmds  += "<?= iconv('UTF-8', 'CP850//TRANSLIT', removeAccents($config->logradouro . ', ' . $config->numero . ', ' . $config->bairro)); ?>\n"
              + "Telefone: <?= $config->fone; ?>\n"
              + ESC + "M" + "\x00"
              + "------------------------------------------\n";
        // Título do cupom
        cmds += ESC + "M" + "\x01";
        cmds += ESC + "a" + "\x01"              // Centraliza o título
             + "Doc. Auxiliar da Nota Fiscal de Consumidor Eletronica\n"
             + "Nao permite aproveitamento de credito de ICMS\n"
             + ESC + "a" + "\x00"              // volta alinhamento à esquerda
        cmds += ESC + "M" + "\x00";     
        cmds += "------------------------------------------\n";
        // Cabeçalho da tabela de produtos em negrito, dividido em duas linhas:
        cmds += ESC + "M" + "\x01"    // Seleciona Font B (menor) 
             +  ESC + "E" + "\x01"; // Ativa negrito
        // Primeira linha: título para a coluna de descrição (40 caracteres)
        cmds += "<?= str_pad("DESCRICAO", 50, " ", STR_PAD_RIGHT); ?>\n";
        // Segunda linha: cabeçalho para as demais colunas, cada uma com 10 caracteres fixos
        cmds += "<?= str_pad("QTD", 14, " ", STR_PAD_RIGHT); ?>"
             + "<?= str_pad("VALOR", 14, " ", STR_PAD_RIGHT); ?>"
             + "<?= str_pad("DESC", 13, " ", STR_PAD_RIGHT); ?>"
             + "<?= str_pad("TOTAL", 14, " ", STR_PAD_LEFT); ?>"
             + "\n";
        cmds += ESC + "E" + "\x00" // Desativa negrito
             + ESC + "M" + "\x00";    // Font A (normal)

        // Lista de produtos, cada item em duas linhas:
        cmds += ESC + "M" + "\x01";  // Seleciona Font B
        <?php foreach($venda->itens as $item): ?>
        <?php 
        // Linha 1: descrição do produto (40 caracteres fixos)
        $desc = iconv('UTF-8', 'CP850//TRANSLIT', removeAccents(str_pad(mb_strimwidth($item->produto->nome, 0, 50, '...'), 50, ' ', STR_PAD_RIGHT)));
        // Prepara cada campo para 10 caracteres
                // Formatação condicional da quantidade:
                $qtdValor = $item->quantidade;
                if (floor($qtdValor) == $qtdValor) {
                    $qtdFormatada = number_format($qtdValor, 0, '.', '.');
                } else {
                    $qtdFormatada = number_format($qtdValor, $config->casas_decimais_qtd, '.', '.');
                }
        $qtd   = str_pad($qtdFormatada, 13, ' ', STR_PAD_RIGHT);
        $valor = str_pad(number_format($item->valor_original, $config->casas_decimais, ',', '.'), 13, ' ', STR_PAD_RIGHT);
        $desc_valor = str_pad(isset($item->desconto) ? number_format($item->desconto * $item->quantidade, $config->casas_decimais, ',', '.') : '0,00', 11, ' ', STR_PAD_RIGHT);
        $total = str_pad(number_format($item->valor * $item->quantidade, $config->casas_decimais, ',', '.'), 14, ' ', STR_PAD_LEFT);
        
        // Monta a linha 2 utilizando substr para forçar cada campo em 10 caracteres e concatenando os símbolos fixos:
        $line2 = substr($qtd, 0, 13)
               . substr(" ", 0, 1)
               . substr($valor, 0, 13)
               . substr(" ", 0, 1)
               . substr($desc_valor, 0, 11)
               . substr(" ", 0, 1)
               . substr($total, 0, 14);
        ?>
        cmds += "<?= $desc; ?>\n" + "<?= $line2; ?>\n";
        <?php endforeach; ?>
        cmds += ESC + "M" + "\x00";  // Volta para Font A
        cmds += "------------------------------------------\n";
        cmds += ESC + "E" + "\x01"
             + "Resumo Geral:\n"
             + ESC + "E" + "\x00";

        // Agora o conteúdo do resumo
        cmds += ESC + "3" + "\x08"
             + ESC + "M\x01"
             + "(=)Valor total dos itens: R$ <?= number_format($totalValorItens, 2, ',', '.'); ?>\n"
             + "(-)Desconto total dos itens: R$ <?= number_format($totalDescontosItens,2,',','.'); ?>\n"
             + "(=)Subtotal da venda: R$ <?= number_format($subtotalFinal,2,',','.'); ?>\n"
             + "(-)Desconto geral: R$ <?= number_format($descontoGeral,2,',','.'); ?>\n"
             + "(+)Acrescimo geral: R$ <?= number_format($acrescimoGeral,2,',','.'); ?>\n"
             + ESC + "E" + "\x01"  // negrito só no total final
             + "(=)Valor total da venda: R$ <?= number_format($valorTotalVenda,2,',','.'); ?>\n"
             + ESC + "M" + "\x00"
             + ESC + "E" + "\x00"
             cmds += ESC + "3" + "\x18"
             + "------------------------------------------\n";
        // ======= PAGAMENTO =======
        cmds += ESC + "3" + "\x08"
             + ESC + "E\x01"           // negrito ON
             + "Pagamento:\n"
             + ESC + "E\x00";           // negrito OFF

        // muda para Font B (menor)
        cmds += ESC + "M\x01";

        <?php foreach($pagamentos as $pag): ?>
            <?php
                $label = iconv(
                    'UTF-8','CP850//TRANSLIT',
                    removeAccents(\App\Models\VendaCaixa::getTipoPagamento($pag->forma_pagamento))
                );
            ?>
            cmds += "<?= $label; ?>: R$ <?= number_format($pag->valor,2,',','.'); ?>\n";
        <?php endforeach; ?>

        // troco também em Font B
        cmds += "Troco: R$ <?= number_format($troco,2,',','.'); ?>\n";

        // volta para Font A (normal) antes do separador
        cmds += ESC + "M\x00";

        cmds += "------------------------------------------\n";

        // **NOVA SEÇÃO**: link de consulta e chave de acesso
        cmds += ESC + "M" + "\x01";    // seleciona Font B (pequena)
        cmds += ESC + "a" + "\x01";      // centraliza
        cmds += ESC + "E" + "\x01"; 
        cmds += "Consulte pela Chave de Acesso em:\n";
        cmds += ESC + "E" + "\x00";       
        cmds += "<?= $consultaUrl ?>\n";
        cmds += "<?= $formattedKey ?>\n";  
        cmds += ESC + "a" + "\x00";     
        cmds += ESC + "M" + "\x00";

// ↓ BLOCO DE CONSUMIDOR ↓
cmds += "------------------------------------------\n";

// entra em Font B (pequena), negrito e centralizado
cmds += ESC + "M"   + "\x01";  // Font B
cmds += ESC + "E"   + "\x01";  // negrito ON
cmds += ESC + "a"   + "\x01";  // centraliza

<?php if($venda->cliente_id): ?>
  // cliente cadastrado
  cmds += "<?= iconv('UTF-8','CP850//TRANSLIT', removeAccents($venda->cliente->razao_social)); ?> - "
       + "<?php
            $doc = preg_replace('/\D/','',$venda->cliente->cpf_cnpj);
            if(strlen($doc)===11) {
                echo 'CPF ' . formatCpf($venda->cliente->cpf_cnpj);
            } else {
                echo 'CNPJ ' . formatCnpj($venda->cliente->cpf_cnpj);
            }
         ?>\n";
  cmds += "<?= iconv('UTF-8','CP850//TRANSLIT',
         removeAccents(
           $venda->cliente->rua . ', ' .
           $venda->cliente->numero . ' - ' .
           $venda->cliente->bairro . ' - ' .
           $venda->cliente->cidade->nome . '-' .
           $venda->cliente->cidade->uf
         )
     ); ?>\n";
<?php elseif(!empty($venda->cpf) || !empty($venda->nome)): ?>
  <?php
    // prepara valores com fallback
    $manualName = $venda->nome
      ? removeAccents($venda->nome)
      : 'CONSUMIDOR FINAL';
    $docRaw = preg_replace('/\D/','',$venda->cpf);
    if (strlen($docRaw) === 11) {
      $manualDoc = 'CPF ' . formatCpf($venda->cpf);
    } elseif (strlen($docRaw) > 0) {
      $manualDoc = 'CNPJ ' . formatCnpj($venda->cpf);
    } else {
      $manualDoc = '000.000.000-00';
    }
  ?>
  // usa sempre nome e CPF/CNPJ, com default se não preenchido
  cmds += "<?= iconv('UTF-8','CP850//TRANSLIT', $manualName); ?> - <?= $manualDoc; ?>\n";
<?php else: ?>
  // nenhum dado
  cmds += "CONSUMIDOR NAO IDENTIFICADO\n";
<?php endif; ?>
        // Número, série e data/hora de emissão em negrito
        cmds += ESC + "E" + "\x01";
        cmds += "NFCe n. {{ str_pad($nnfXml, 9, '0', STR_PAD_LEFT) }}  Serie {{ $serieXml }}  {{ $dhEmi }}\n";
        cmds += ESC + "E" + "\x00";

        // Protocolo e data de autorização, ainda centralizado
        cmds += "Protocolo de Autorizacao: {{ $nProt }}\n";
        cmds += "Data de Autorizacao: {{ $dhRecb }}\n";

        cmds += ESC + "a" + "\x00";
        // Volta para Font A (normal)
        cmds += ESC + "3" + "\x18"
        cmds += ESC + "M" + "\x00";

        // agora, continue com os tributos e pós-tributos:
        // 2) Leia as linhas do preview
        const tribPreviewEl = document.getElementById("tributos-preview");
        const tribLines = tribPreviewEl.innerText
          .split("\n")
          .map(l => l.trim())
          .filter(l => l);

        // 3) Ache a linha “Trib. aprox…” (fallback se não existir)
        const approxLine = tribLines.find(l => l.startsWith("Trib. aprox")) || "";

        // 4) Monte cmdsTrib CENTRALIZADO em Font B
        let cmdsTrib = "";
        // alinha ao centro
        cmdsTrib += ESC + "a" + "\x01";
        // Font B (menor)
        cmdsTrib += ESC + "M" + "\x01";
        // imprime
        cmdsTrib += approxLine + "\n";
        // volta a Font A
        cmdsTrib += ESC + "M" + "\x00";
        // alinha à esquerda novamente
        cmdsTrib += ESC + "a" + "\x00";

        cmdsTrib  += "------------------------------------------\n"; 
        // ————————————————————————————————————————————————————————————————
    
        let cmdsPosTributos = "";
        // Observação (se existir)
        <?php if(!empty($venda->observacao)): ?>
            cmdsPosTributos  += ESC + "M" + "\x01"  
                             +  "Observacao: <?= iconv('UTF-8', 'CP850//TRANSLIT', removeAccents($venda->observacao)); ?>\n"
                             +  ESC + "M" + "\x00"
            cmdsPosTributos  += "------------------------------------------\n";
        <?php endif; ?>
        // Mensagem Padrão centralizada (se existir)
        <?php if(!empty($configCaixa->mensagem_padrao_cupom)): ?>
            cmdsPosTributos  += ESC + "a" + "\x01"  // Centraliza
                  + "<?= iconv('UTF-8', 'CP850//TRANSLIT', removeAccents($configCaixa->mensagem_padrao_cupom)); ?>\n"
                  + ESC + "a" + "\x00";  // Volta para alinhamento à esquerda
        <?php endif; ?>
        cmdsPosTributos  += "\n";
        // Comando para cortar o papel – ajuste conforme o manual da impressora
        cmdsPosTributos  += GS + "V" + "\x01";

        // 7) Extrai Base64 do QR (<img>)
        const imgEl  = document.querySelector('#qr-preview img');
        const base64 = imgEl.src.split(',')[1];

        // 8) Comando de impressão do QR como imagem
        const imageCmd = {
            type:   'raw',
            format: 'image',
            flavor: 'base64',
            data:   base64,
            options:{ language:'ESCPOS', dotDensity:'double' }
        };

        // 9) Chama o print **com todos** objetos tendo `data`
        try {
          await qz.print(cfg, [
            { type:'raw', format:'command', data: cmds },
            { type:'raw', format:'command', data: ESC + 'a' + '\x01' },
            imageCmd,
            { type:'raw', format:'command', data: ESC + 'a' + '\x00' },
            { type:'raw', format:'command', data: cmdsTrib },
            { type:'raw', format:'command', data: cmdsPosTributos }
        ]);
            setTimeout(() => window.close(), 500);
        } catch (err) {
            console.error("✘ Falha na impressão:", err);
        }
});
</script>
</body>
</html>