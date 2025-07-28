<?php

namespace App\Utility;

use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;

/**
 * Class PdfGenerator
 * @package App\Utility
 *
 * Utilitário para gerar documentos PDF a partir de conteúdo HTML, usando Dompdf.
 */
class PdfGenerator
{
    /**
     * Gera um PDF a partir de uma string HTML.
     *
     * @param string $htmlContent O conteúdo HTML a ser renderizado no PDF.
     * @param string $filename O nome do arquivo PDF (sem extensão) para download ou salvamento.
     * @param bool $stream Define se o PDF deve ser enviado diretamente para o navegador (true) ou retornado como string (false).
     * @param string $paperSize O tamanho do papel (ex: 'A4', 'letter').
     * @param string $orientation A orientação do papel ('portrait' ou 'landscape').
     * @return string|void Retorna o conteúdo binário do PDF se $stream for false, caso contrário, envia para o navegador e encerra a execução.
     * @throws Exception Se houver um erro na geração do PDF.
     */
    public static function generatePdf(
        string $htmlContent,
        string $filename = 'document',
        bool $stream = true,
        string $paperSize = 'A4',
        string $orientation = 'portrait'
    ) {
        // Configurações do Dompdf
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans'); // Recomenda-se usar uma fonte que suporte caracteres UTF-8
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Permite carregar imagens de URLs externas, se necessário

        $dompdf = new Dompdf($options);

        // Carrega o HTML
        $dompdf->loadHtml($htmlContent);

        // Define o tamanho e orientação do papel
        $dompdf->setPaper($paperSize, $orientation);

        // Renderiza o HTML em PDF
        $dompdf->render();

        if ($stream) {
            // Envia o PDF para o navegador para download
            $dompdf->stream($filename . '.pdf', ["Attachment" => true]);
            exit(0); // Garante que nenhum outro conteúdo seja enviado após o PDF
        } else {
            // Retorna o conteúdo binário do PDF
            return $dompdf->output();
        }
    }
}