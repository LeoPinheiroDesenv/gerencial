<?php
namespace App\Utils;

use NFePHP\DA\NFe\Danfce as BaseDanfce;

class CustomDanfce extends BaseDanfce
{
    /**
     * Retorna a string de QR-Code (conteúdo de <qrCode> do XML).
     */
    public function getQrCodeString(): ?string
    {
        return $this->qrCode;
    }
}
