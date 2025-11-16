<?php

namespace NFePHP\DA\NFe\Traits;

/**
 * Bloco forma de pagamento
 */
trait TraitBlocoV
{
    protected function blocoV($y)
    {
        $this->bloco5H = $this->calculateHeightPag();

        $aFont = ['font'=> $this->fontePadrao, 'size' => 7, 'style' => ''];
        //$this->pdf->textBox($this->margem, $y, $this->wPrint, $this->bloco5H, '', $aFont, 'T', 'C', true, '', false);
        $arpgto = [];

        // CORREÇÃO: Tratamento robusto para diferentes tipos de elemento de pagamento
        if ($this->pag instanceof \DOMNodeList && $this->pag->length > 0) {
            // Se é DOMNodeList com itens
            foreach ($this->pag as $pgto) {
                $tipo = $this->pagType((int) $this->getTagValue($pgto, 'tPag'));
                $valor = number_format((float) $this->getTagValue($pgto, 'vPag'), 2, ',', '.');
                $arpgto[] = [
                    'tipo' => $tipo,
                    'valor' => $valor
                ];
            }
        } elseif ($this->pag instanceof \DOMElement) {
            // Se é um único DOMElement
            $tipo = $this->pagType((int) $this->getTagValue($this->pag, 'tPag'));
            $valor = number_format((float) $this->getTagValue($this->pag, 'vPag'), 2, ',', '.');
            $arpgto[] = [
                'tipo' => $tipo,
                'valor' => $valor
            ];
        } else {
            // Fallback: usa valor total da NF
            $valorNF = $this->getTagValue($this->ICMSTot, 'vNF');
            $arpgto[] = [
                'tipo' => 'Dinheiro',
                'valor' => number_format((float) $valorNF, 2, ',', '.')
            ];
        }

        $aFont = ['font'=> $this->fontePadrao, 'size' => 7, 'style' => 'B'];
        $texto = "FORMA PAGAMENTO";
        $this->pdf->textBox($this->margem, $y, $this->wPrint, 4, $texto, $aFont, 'T', 'L', false, '', false);
        $texto = "VALOR PAGO R$";
        $y1 = $this->pdf->textBox($this->margem, $y, $this->wPrint, 4, $texto, $aFont, 'T', 'R', false, '', false);

        $z = $y + $y1;
        foreach ($arpgto as $p) {
            $aFont = ['font'=> $this->fontePadrao, 'size' => 6, 'style' => ''];
            $this->pdf->textBox($this->margem, $z, $this->wPrint, 3, $p['tipo'], $aFont, 'T', 'L', false, '', false);
            $aFont = ['font'=> $this->fontePadrao, 'size' => 7, 'style' => ''];
            $y2 = $this->pdf->textBox(
                $this->margem,
                $z,
                $this->wPrint,
                3,
                $p['valor'],
                $aFont,
                'T',
                'R',
                false,
                '',
                false
            );
            $z += $y2;
        }

        // Troco (apenas se existir)
        if (!empty($this->vTroco)) {
            $texto = "Troco R$";
            $this->pdf->textBox($this->margem, $z, $this->wPrint, 3, $texto, $aFont, 'T', 'L', false, '', false);
            $texto = number_format((float) $this->vTroco, 2, ',', '.');
            $y1 = $this->pdf->textBox($this->margem, $z, $this->wPrint, 3, $texto, $aFont, 'T', 'R', false, '', false);
            $z += $y1;
        }

        $this->pdf->dashedHLine($this->margem, $z, $this->wPrint, 0.1, 30);
        return $z + 2; // Retorna a posição Y final
    }

    protected function pagType($type)
    {
        $lista = [
            1 => 'Dinheiro',
            2 => 'Cheque',
            3 => 'Cartão de Crédito',
            4 => 'Cartão de Débito',
            5 => 'Cartão da Loja/Outros Crediários',
            10 => 'Vale Alimentação',
            11 => 'Vale Refeição',
            12 => 'Vale Presente',
            13 => 'Vale Combustível',
            15 => 'Boleto Bancário',
            16 => 'Depósito Bancário',
            17 => 'Pagamento Instantâneo (PIX) - Dinâmico',
            18 => 'Transferência bancária, Carteira Digital',
            19 => 'Programa fidelidade, Cashback, Créd Virt',
            20 => 'Pagamento Instantâneo (PIX) - Estático',
            21 => 'Crédito em Loja',
            22 => 'Pagamento Eletrônico não Informado - falha de hardware do sistema emissor',
            90 => 'Sem pagamento',
            91 => 'Pagamento Posterior',
            99 => 'Outros',
        ];
        return mb_strtoupper($lista[$type]) ?? 'Outros';
    }

    protected function calculateHeightPag()
    {
        $n = 1; // Mínimo 1 linha
        if ($this->pag instanceof \DOMNodeList) {
            $n = max($this->pag->length, 1);
        }
        $height = 4 + (2.4 * $n) + 3;
        return $height;
    }
}
