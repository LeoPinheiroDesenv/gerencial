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

  <!-- Conteúdo visual para exibição (opcional) -->
  <div class="container">
    <!-- Cabeçalho com dados da empresa -->
    <div class="header">
      <?php if(!empty($config->logo)): ?>
        <img src="<?= asset('logos/' . $config->logo) ?>" alt="Logo">
      <?php endif; ?>
      <div class="header-info">
        <?= removeAccents($config->razao_social); ?><br>
        <?= removeAccents($config->logradouro . ', ' . $config->numero . ', ' . $config->bairro); ?><br>
        Telefone: <?= $config->fone; ?>
      </div>
    </div>
    <hr class="dashed">
    <!-- Título do cupom -->
    <div class="header-secondary">
      <h3 style="text-align:center;">FATURAMENTO DE VENDA</h3>
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

  <!-- Código para enviar os comandos ESC/POS via QZ Tray -->
<script src="/js/qz-tray.js" defer></script>
<script defer>
document.addEventListener("DOMContentLoaded", function(){
  // 1) Certificado
  qz.security.setCertificatePromise((res, rej) => {
    console.log("→ carregando digital-certificate.txt...");
    fetch('/assets/qz-certs/digital-certificate.txt',{cache:'no-store'})
      .then(r => r.ok ? r.text() : Promise.reject(r.statusText))
      .then(cert => { console.log("✓ Certificado público carregado"); res(cert); })
      .catch(err => { console.error("✘ Erro ao carregar certificado:", err); rej(err); });
  });

  // 2) Algoritmo
  qz.security.setSignatureAlgorithm('SHA512');

  // 3) Assinatura
  qz.security.setSignaturePromise(function(toSign) {
  console.log("→ solicitando assinatura para:", toSign);
  // Retornamos uma função que o QZ Tray chamará com (resolve, reject)
  return function(resolve, reject) {
    fetch('/sign-message.php?request=' + encodeURIComponent(toSign), { cache: 'no-store' })
      .then(function(response) {
        if (!response.ok) throw new Error(response.statusText);
        return response.text();
      })
      .then(function(signature) {
        console.log("✓ Assinatura recebida:", signature.substring(0,20) + "…");
        resolve(signature);
      })
      .catch(function(err) {
        console.error("✘ Erro ao assinar:", err);
        reject(err);
      });
  };
});

        var ESC = "\x1B";  // Código ESC
        var GS  = "\x1D";  // Código GS

        // Seleciona a code page – tente "\x00" ou "\x01" conforme a impressora
        var selectCodePage = ESC + "t" + "\x00";

        // Monta os comandos ESC/POS para o cupom
        var cmds = "";
        cmds += selectCodePage;
        // Cabeçalho com dados da empresa (convertidos para CP850)
        cmds += ESC + "@"                           // Inicializa a impressora
              + ESC + "a" + "\x01"                    // Centraliza
              + "<?= iconv('UTF-8', 'CP850//TRANSLIT', removeAccents($config->razao_social)); ?>\n"
              + "<?= iconv('UTF-8', 'CP850//TRANSLIT', removeAccents($config->logradouro . ', ' . $config->numero . ', ' . $config->bairro)); ?>\n"
              + "Telefone: <?= $config->fone; ?>\n"
              + "------------------------------------------\n";
        // Título do cupom
        cmds += ESC + "E" + "\x01"              // Ativa negrito
             + ESC + "a" + "\x01"              // Centraliza o título
             + "FATURAMENTO DE VENDA\n"
             + ESC + "a" + "\x00"              // Alinha à esquerda
             + ESC + "E" + "\x00";             // Desativa negrito
        cmds += "------------------------------------------\n";
// Cabeçalho da tabela de produtos em negrito, dividido em duas linhas:
cmds += ESC + "E" + "\x01"; // Ativa negrito
// Primeira linha: título para a coluna de descrição (40 caracteres)
cmds += "<?= str_pad("DESCRICAO", 40, " ", STR_PAD_RIGHT); ?>\n";
// Segunda linha: cabeçalho para as demais colunas, cada uma com 10 caracteres fixos
cmds += "<?= str_pad("QTD", 11, " ", STR_PAD_RIGHT); ?>"
     + "<?= str_pad("VALOR", 11, " ", STR_PAD_RIGHT); ?>"
     + "<?= str_pad("DESC", 10, " ", STR_PAD_RIGHT); ?>"
     + "<?= str_pad("TOTAL", 10, " ", STR_PAD_LEFT); ?>"
     + "\n";
cmds += ESC + "E" + "\x00"; // Desativa negrito
cmds += "------------------------------------------\n";

// Lista de produtos, cada item em duas linhas:
<?php foreach($venda->itens as $item): ?>
    <?php 
        // Linha 1: descrição do produto (40 caracteres fixos)
        $desc = iconv('UTF-8', 'CP850//TRANSLIT', removeAccents(str_pad(mb_strimwidth($item->produto->nome, 0, 40, '...'), 40, ' ', STR_PAD_RIGHT)));
        // Prepara cada campo para 10 caracteres
                // Formatação condicional da quantidade:
                $qtdValor = $item->quantidade;
                if (floor($qtdValor) == $qtdValor) {
                    $qtdFormatada = number_format($qtdValor, 0, '.', '.');
                } else {
                    $qtdFormatada = number_format($qtdValor, $config->casas_decimais_qtd, '.', '.');
                }
        $qtd   = str_pad($qtdFormatada, 10, ' ', STR_PAD_RIGHT);
        $valor = str_pad(number_format($item->valor_original, $config->casas_decimais, ',', '.'), 10, ' ', STR_PAD_RIGHT);
        $desc_valor = str_pad(isset($item->desconto) ? number_format($item->desconto * $item->quantidade, $config->casas_decimais, ',', '.') : '0,00', 9, ' ', STR_PAD_RIGHT);
        $total = str_pad(number_format($item->valor * $item->quantidade, $config->casas_decimais, ',', '.'), 10, ' ', STR_PAD_LEFT);
        
        // Monta a linha 2 utilizando substr para forçar cada campo em 10 caracteres e concatenando os símbolos fixos:
        $line2 = substr($qtd, 0, 10)
               . substr(" ", 0, 1)
               . substr($valor, 0, 10)
               . substr(" ", 0, 1)
               . substr($desc_valor, 0, 9)
               . substr(" ", 0, 1)
               . substr($total, 0, 10);
    ?>
    cmds += "<?= $desc; ?>\n" + "<?= $line2; ?>\n";
<?php endforeach; ?>


        cmds += "------------------------------------------\n";
        // Fechamento com dados adicionais
                // Fechamento com dados adicionais
        // Centraliza e liga o negrito
        cmds += "\x1B\x61\x01";   // ESC a 1 = alinhamento central
        cmds += "\x1B\x45\x01";   // ESC E 1 = negrito ON

        // Texto do fechamento
        cmds += "FECHAMENTO\n";

        // Desliga o negrito e volta para alinhamento à esquerda
        cmds += "\x1B\x45\x00";   // ESC E 0 = negrito OFF
        cmds += "\x1B\x61\x00";   // ESC a 0 = alinhamento esquerdo
        cmds += "------------------------------------------\n";
        cmds += "Codigo da venda: <?= $venda->id; ?>\n"
             + "Data: <?= \Carbon\Carbon::parse($venda->created_at)->format('d/m/Y H:i:s'); ?>\n"
             + ESC + "E" + "\x01" + "Vendedor: <?= iconv('UTF-8', 'CP850//TRANSLIT', removeAccents($venda->vendedor())); ?>" + ESC + "E" + "\x00" + "\n"
        <?php if($venda->cliente_id): ?>
        // === Dados do cliente ===
        cmds += "Cliente: <?= iconv('UTF-8','CP850//TRANSLIT', removeAccents($venda->cliente->razao_social)); ?>\n"
             + "CPF/CNPJ: <?= iconv('UTF-8','CP850//TRANSLIT', removeAccents($venda->cliente->cpf_cnpj)); ?>\n"
             + "Endereco: <?= iconv('UTF-8','CP850//TRANSLIT', removeAccents($venda->cliente->rua . ', ' . $venda->cliente->numero . ' - ' . $venda->cliente->bairro . ' - ' . $venda->cliente->cidade->nome . ' (' . $venda->cliente->cidade->uf . ')')); ?>\n"
             + "Telefone: <?= iconv('UTF-8','CP850//TRANSLIT', removeAccents($venda->cliente->telefone . ' - ' . $venda->cliente->celular)); ?>\n"
             + "------------------------------------------\n";
        <?php endif; ?>
             + "------------------------------------------\n";
        // **AQUI** inserimos o subtítulo em negrito:
        // Subtítulo "Resumo Geral:" em negrito, alinhado à esquerda
        cmds += ESC + "E" + "\x01"
             + "Resumo Geral:\n"
             + ESC + "E" + "\x00";

        // Agora o conteúdo do resumo
        cmds += "(=)Valor total dos itens: R$ <?= number_format($totalValorItens, 2, ',', '.'); ?>\n"
             + "(-)Desconto total dos itens: R$ <?= number_format($totalDescontosItens,2,',','.'); ?>\n"
             + "(=)Subtotal da venda: R$ <?= number_format($subtotalFinal,2,',','.'); ?>\n"
             + "(-)Desconto geral: R$ <?= number_format($descontoGeral,2,',','.'); ?>\n"
             + "(+)Acrescimo geral: R$ <?= number_format($acrescimoGeral,2,',','.'); ?>\n"
             + ESC + "E" + "\x01"  // negrito só no total final
             + "(=)Valor total da venda: R$ <?= number_format($valorTotalVenda,2,',','.'); ?>\n"
             + ESC + "E" + "\x00"
             + "------------------------------------------\n";
    // ======= PAGAMENTO =======
    cmds += ESC + "E" + "\x01"   // negrito ON
         + "Pagamento:\n"
         + ESC + "E" + "\x00";   // negrito OFF

    <?php foreach($pagamentos as $pag): ?>
        <?php
            $label = iconv(
                'UTF-8','CP850//TRANSLIT',
                removeAccents(\App\Models\VendaCaixa::getTipoPagamento($pag->forma_pagamento))
            );
        ?>
        cmds += "<?= $label; ?>: R$ <?= number_format($pag->valor,2,',','.'); ?>"
            <?php if($pag->forma_pagamento === '06'): ?>
                + " venc: <?= $pag->data_vencimento; ?> (  )"
            <?php endif; ?>
            + "\n";
    <?php endforeach; ?>

    cmds += "Troco: R$ <?= number_format($troco,2,',','.'); ?>\n"
         + "------------------------------------------\n";
        // Observação (se existir)
        <?php if(!empty($venda->observacao)): ?>
            cmds += "Observacao: <?= iconv('UTF-8', 'CP850//TRANSLIT', removeAccents($venda->observacao)); ?>\n";
            cmds += "------------------------------------------\n";
        <?php endif; ?>
        // Mensagem Padrão centralizada (se existir)
        <?php if(!empty($configCaixa->mensagem_padrao_cupom)): ?>
            cmds += ESC + "a" + "\x01"  // Centraliza
                  + "<?= iconv('UTF-8', 'CP850//TRANSLIT', removeAccents($configCaixa->mensagem_padrao_cupom)); ?>\n"
                  + ESC + "a" + "\x00";  // Volta para alinhamento à esquerda
        <?php endif; ?>
        cmds += "\n";
        // Comando para cortar o papel – ajuste conforme o manual da impressora
        cmds += GS + "V" + "\x01";

  // 5) Conexão e impressão
  console.log("🔐 Iniciando conexão com QZ Tray...");
  qz.websocket.connect()
    .then(() => { console.log("1) WebSocket conectado"); return qz.printers.getDefault(); })
    .then(printer => {
      console.log("2) Impressora padrão:", printer);
      var cfg = qz.configs.create(printer,{copies:1,options:{dialog:false,forceRaw:true}});
      return qz.print(cfg,[cmds]);
    })
    .then(() => {
      console.log("3) Comando de impressão enviado");
     // ↓ 3) Fecha a janela 500ms após o envio, para garantir transmissão completa
     setTimeout(function(){ window.close(); }, 500);
    })
    .catch(err => console.error("✘ Falha em alguma etapa:", err));
});
</script>
</body>
</html>