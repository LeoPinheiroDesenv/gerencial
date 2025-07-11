<div class="row">
    @foreach($item->fatura as $d)
    <div class="col-lg-4 col-12">
        <div class="card">
            <div class="card-body">
                <h4>{{ \App\Models\VendaCaixa::getTipoPagamento(str_pad(trim($d->forma_pagamento), 2, '0', STR_PAD_LEFT)) }}</h4>
                <h4>R${{ moeda($d->valor) }}</h4>
            </div>
        </div>
    </div>
    @endforeach
</div>
