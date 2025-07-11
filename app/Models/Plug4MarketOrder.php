<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Plug4MarketOrder extends Model
{
    protected $table = 'plug4market_orders';

    protected $fillable = [
        'external_id',
        'order_number',
        'marketplace',
        'status',
        'shipping_cost',
        'shipping_name',
        'payment_name',
        'interest',
        'total_commission',
        'type_billing',
        'total_amount',
        
        // Dados de entrega (shipping)
        'shipping_recipient_name',
        'shipping_phone',
        'shipping_street',
        'shipping_street_number',
        'shipping_city',
        'shipping_street_complement',
        'shipping_country',
        'shipping_district',
        'shipping_state',
        'shipping_zip_code',
        'shipping_ibge',
        
        // Dados de cobrança (billing)
        'billing_name',
        'billing_email',
        'billing_document_id',
        'billing_state_registration_id',
        'billing_street',
        'billing_street_number',
        'billing_street_complement',
        'billing_district',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_zip_code',
        'billing_phone',
        'billing_gender',
        'billing_date_of_birth',
        'billing_tax_payer',
        'billing_ibge',
        
        // Relacionamentos
        'cliente_id',
        
        // Controle
        'sincronizado',
        'ultima_sincronizacao',
        'raw_data',
        
        // Novos campos para nota
        'invoice_number',
        'invoice_key',
        'invoice_date',
        'invoice_url',
        'invoice_status',
        'invoice_payload',
        
        // Campos para XML da nota fiscal
        'invoice_xml',
        'invoice_xml_filename',
        'invoice_xml_path',
        'invoice_xml_downloaded_at',
        'invoice_xml_status',
        'invoice_xml_error_message',
        
        // Campos para arquivo da nota fiscal
        'invoice_file_name',
        'invoice_file_path',
        'invoice_file_size',
        'invoice_file_type',
        'invoice_file_uploaded_at',
        
        // Campos adicionais para controle da nota fiscal
        'invoice_series',
        'invoice_model',
        'invoice_environment',
        'invoice_protocol',
        'invoice_protocol_date',
        'invoice_total_products',
        'invoice_total_taxes',
        'invoice_total_shipping',
        'invoice_total_discount',
        'invoice_total_final'
    ];

    protected $casts = [
        'raw_data' => 'array',
        'invoice_payload' => 'array',
        'invoice_date' => 'datetime',
        'invoice_xml_downloaded_at' => 'datetime',
        'invoice_protocol_date' => 'datetime',
        'invoice_file_uploaded_at' => 'datetime',
        'invoice_total_products' => 'decimal:2',
        'invoice_total_taxes' => 'decimal:2',
        'invoice_total_shipping' => 'decimal:2',
        'invoice_total_discount' => 'decimal:2',
        'invoice_total_final' => 'decimal:2',
        'sincronizado' => 'boolean',
        'billing_tax_payer' => 'boolean'
    ];

    public function items()
    {
        return $this->hasMany(Plug4MarketOrderItem::class, 'order_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function getStatusTextAttribute()
    {
        $statuses = [
            1 => 'Pendente',
            2 => 'Confirmado',
            3 => 'Enviado',
            4 => 'Entregue',
            5 => 'Cancelado'
        ];

        return $statuses[$this->status] ?? 'Status ' . $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        $statusColors = [
            1 => 'warning',   // Pendente
            2 => 'info',      // Confirmado
            3 => 'primary',   // Enviado
            4 => 'success',   // Entregue
            5 => 'danger'     // Cancelado
        ];

        return $statusColors[$this->status] ?? 'secondary';
    }

    public function getTypeBillingTextAttribute()
    {
        return $this->type_billing === 'PJ' ? 'Pessoa Jurídica' : 'Pessoa Física';
    }

    public function getFormattedTotalAmountAttribute()
    {
        return 'R$ ' . number_format($this->total_amount, 2, ',', '.');
    }

    public function getFormattedShippingCostAttribute()
    {
        return 'R$ ' . number_format($this->shipping_cost, 2, ',', '.');
    }

    public function getFormattedTotalCommissionAttribute()
    {
        return 'R$ ' . number_format($this->total_commission, 2, ',', '.');
    }

    public function getShippingAddressAttribute()
    {
        $parts = [];
        
        if ($this->shipping_street) {
            $parts[] = $this->shipping_street;
            if ($this->shipping_street_number) {
                $parts[] = $this->shipping_street_number;
            }
        }
        
        if ($this->shipping_district) {
            $parts[] = $this->shipping_district;
        }
        
        if ($this->shipping_city) {
            $cityState = $this->shipping_city;
            if ($this->shipping_state) {
                $cityState .= '/' . $this->shipping_state;
            }
            $parts[] = $cityState;
        }
        
        if ($this->shipping_zip_code) {
            $parts[] = $this->shipping_zip_code;
        }
        
        return implode(', ', $parts);
    }

    public function getBillingAddressAttribute()
    {
        $parts = [];
        
        if ($this->billing_street) {
            $parts[] = $this->billing_street;
            if ($this->billing_street_number) {
                $parts[] = $this->billing_street_number;
            }
        }
        
        if ($this->billing_district) {
            $parts[] = $this->billing_district;
        }
        
        if ($this->billing_city) {
            $cityState = $this->billing_city;
            if ($this->billing_state) {
                $cityState .= '/' . $this->billing_state;
            }
            $parts[] = $cityState;
        }
        
        if ($this->billing_zip_code) {
            $parts[] = $this->billing_zip_code;
        }
        
        return implode(', ', $parts);
    }

    public function scopeSincronizados($query)
    {
        return $query->where('sincronizado', true);
    }

    public function scopeNaoSincronizados($query)
    {
        return $query->where('sincronizado', false);
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorMarketplace($query, $marketplace)
    {
        return $query->where('marketplace', $marketplace);
    }

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    /**
     * Verificar se o pedido tem nota fiscal
     */
    public function hasInvoice()
    {
        return !empty($this->invoice_number) && !empty($this->invoice_key);
    }

    /**
     * Verificar se o XML da nota fiscal foi baixado
     */
    public function hasInvoiceXml()
    {
        return !empty($this->invoice_xml) && !empty($this->invoice_xml_status) && $this->invoice_xml_status === 'downloaded';
    }

    public function hasInvoiceFile()
    {
        return !empty($this->invoice_file_path) && !empty($this->invoice_file_name);
    }

    public function getInvoiceFileSizeFormattedAttribute()
    {
        if (!$this->invoice_file_size) {
            return 'N/A';
        }

        $size = (int) $this->invoice_file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    public function getInvoiceFileTypeTextAttribute()
    {
        $typeMap = [
            'application/pdf' => 'PDF',
            'text/xml' => 'XML',
            'application/xml' => 'XML',
            'image/jpeg' => 'JPEG',
            'image/jpg' => 'JPG',
            'image/png' => 'PNG'
        ];

        return $typeMap[$this->invoice_file_type] ?? $this->invoice_file_type ?? 'Desconhecido';
    }

    /**
     * Verificar se o pedido está faturado
     */
    public function isInvoiced()
    {
        return $this->hasInvoice() && $this->invoice_status === 'approved';
    }

    /**
     * Obter o XML da nota fiscal como objeto SimpleXMLElement
     */
    public function getInvoiceXmlObject()
    {
        if (!$this->hasInvoiceXml()) {
            return null;
        }

        try {
            return simplexml_load_string($this->invoice_xml);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extrair dados do XML da nota fiscal
     */
    public function extractInvoiceDataFromXml()
    {
        $xml = $this->getInvoiceXmlObject();
        
        if (!$xml) {
            return false;
        }

        try {
            // Extrair dados básicos da NFe
            $nfe = $xml->NFe ?? $xml;
            $infNFe = $nfe->infNFe ?? null;
            
            if (!$infNFe) {
                return false;
            }

            // Dados da nota
            $ide = $infNFe->ide ?? null;
            $total = $infNFe->total ?? null;
            $protNFe = $xml->protNFe ?? null;

            if ($ide) {
                $this->invoice_series = (string) ($ide->serie ?? '');
                $this->invoice_model = (string) ($ide->mod ?? '');
                $this->invoice_environment = (string) ($ide->tpAmb ?? '');
            }

            if ($total && $total->ICMSTot) {
                $this->invoice_total_products = (float) ($total->ICMSTot->vProd ?? 0);
                $this->invoice_total_taxes = (float) ($total->ICMSTot->vICMS ?? 0);
                $this->invoice_total_shipping = (float) ($total->ICMSTot->vFrete ?? 0);
                $this->invoice_total_discount = (float) ($total->ICMSTot->vDesc ?? 0);
                $this->invoice_total_final = (float) ($total->ICMSTot->vNF ?? 0);
            }

            if ($protNFe && $protNFe->infProt) {
                $this->invoice_protocol = (string) ($protNFe->infProt->nProt ?? '');
                $this->invoice_protocol_date = (string) ($protNFe->infProt->dhRecbto ?? '');
            }

            $this->save();
            return true;

        } catch (\Exception $e) {
            Log::error('Erro ao extrair dados do XML da nota fiscal', [
                'order_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Salvar XML da nota fiscal
     */
    public function saveInvoiceXml($xmlContent, $filename = null)
    {
        try {
            $this->invoice_xml = $xmlContent;
            $this->invoice_xml_filename = $filename ?: ($this->invoice_key . '.xml');
            $this->invoice_xml_path = 'xml_plug4market/' . $this->invoice_xml_filename;
            $this->invoice_xml_downloaded_at = now();
            $this->invoice_xml_status = 'downloaded';
            $this->invoice_xml_error_message = null;
            
            $this->save();

            // Extrair dados do XML
            $this->extractInvoiceDataFromXml();

            return true;
        } catch (\Exception $e) {
            $this->invoice_xml_status = 'error';
            $this->invoice_xml_error_message = $e->getMessage();
            $this->save();

            Log::error('Erro ao salvar XML da nota fiscal', [
                'order_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Marcar erro no download do XML
     */
    public function markInvoiceXmlError($errorMessage)
    {
        $this->invoice_xml_status = 'error';
        $this->invoice_xml_error_message = $errorMessage;
        $this->save();
    }

    /**
     * Obter URL para download do XML
     */
    public function getInvoiceXmlDownloadUrl()
    {
        if ($this->hasInvoiceXml()) {
            return route('plug4market.orders.download-xml', ['order' => $this->id]);
        }
        return null;
    }

    /**
     * Obter URL para visualização do XML
     */
    public function getInvoiceXmlViewUrl()
    {
        if ($this->hasInvoiceXml()) {
            return route('plug4market.orders.view-xml', ['order' => $this->id]);
        }
        return null;
    }
} 