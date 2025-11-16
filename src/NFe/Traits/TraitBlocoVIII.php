<?php
namespace NFePHP\DA\NFe\Traits;

use Com\Tecnick\Barcode\Barcode;

/**
 * Bloco QRCode
 */
trait TraitBlocoVIII
{
    protected function blocoVIII($y)
    {
        $this->bloco8H = 50;
        $y += 1;

        // Só gera QR Code se existir
        if (empty($this->qrCode)) {
            // Se não tem QR Code, retorna altura mínima
            return $y + 10;
        }

        try {
            $maxW = $this->wPrint;
            $w = ($maxW * 1) + 4;
            $barcode = new Barcode();
            $bobj = $barcode->getBarcodeObj(
                'QRCODE,M',
                $this->qrCode,
                -4,
                -4,
                'black',
                array(-2, -2, -2, -2)
            )->setBackgroundColor('white');

            $qrcode = $bobj->getPngData();
            $wQr = 50;
            $hQr = 50;
            $yQr = $y;
            $xQr = ($w / 2) - ($wQr / 2);
            $pic = 'data://text/plain;base64,' . base64_encode($qrcode);

            $this->pdf->image($pic, $xQr, $yQr, $wQr, $hQr, 'PNG');

        } catch (\Exception $e) {
            $aFont = ['font' => $this->fontePadrao, 'size' => 6, 'style' => ''];
            $this->pdf->textBox(
                $this->margem,
                $y,
                $this->wPrint,
                10,
                "QR Code não disponível",
                $aFont,
                'T',
                'C'
            );
        }

        return $y + $this->bloco8H;
    }
}
