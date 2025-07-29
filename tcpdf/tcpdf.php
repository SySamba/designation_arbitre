<?php
/**
 * Version simplifiée de TCPDF pour la génération de PDF
 */

class TCPDF {
    private $pageWidth = 297; // A4 landscape
    private $pageHeight = 210;
    private $margin = 15;
    private $x = 15;
    private $y = 15;
    private $fontSize = 10;
    private $fontFamily = 'helvetica';
    private $fontStyle = '';
    private $fillColor = array(255, 255, 255);
    private $textColor = array(0, 0, 0);
    private $pages = array();
    private $currentPage = 0;
    
    public function __construct($orientation = 'L', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false) {
        $this->pages[0] = '';
        $this->currentPage = 0;
    }
    
    public function SetCreator($creator) {
        // Ignoré dans cette version simplifiée
    }
    
    public function SetAuthor($author) {
        // Ignoré dans cette version simplifiée
    }
    
    public function SetTitle($title) {
        // Ignoré dans cette version simplifiée
    }
    
    public function setPrintHeader($print) {
        // Ignoré dans cette version simplifiée
    }
    
    public function setPrintFooter($print) {
        // Ignoré dans cette version simplifiée
    }
    
    public function SetMargins($left, $top, $right = -1) {
        $this->margin = $left;
        $this->x = $left;
        $this->y = $top;
    }
    
    public function AddPage() {
        $this->currentPage++;
        $this->pages[$this->currentPage] = '';
        $this->x = $this->margin;
        $this->y = $this->margin;
    }
    
    public function SetFont($family, $style = '', $size = 0) {
        $this->fontFamily = $family;
        $this->fontStyle = $style;
        if ($size > 0) {
            $this->fontSize = $size;
        }
    }
    
    public function SetXY($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }
    
    public function SetY($y) {
        $this->y = $y;
    }
    
    public function SetX($x) {
        $this->x = $x;
    }
    
    public function SetFillColor($r, $g = null, $b = null) {
        if (is_array($r)) {
            $this->fillColor = $r;
        } else {
            $this->fillColor = array($r, $g, $b);
        }
    }
    
    public function SetTextColor($r, $g = null, $b = null) {
        if (is_array($r)) {
            $this->textColor = $r;
        } else {
            $this->textColor = array($r, $g, $b);
        }
    }
    
    public function Cell($w, $h, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        $style = "position: absolute; left: {$this->x}mm; top: {$this->y}mm; width: {$w}mm; height: {$h}mm; ";
        $style .= "font-family: {$this->fontFamily}; font-size: {$this->fontSize}pt; ";
        
        if ($this->fontStyle == 'B') {
            $style .= "font-weight: bold; ";
        }
        
        if ($border) {
            $style .= "border: 1px solid #000; ";
        }
        
        if ($fill) {
            $rgb = implode(',', $this->fillColor);
            $style .= "background-color: rgb({$rgb}); ";
        }
        
        $rgb = implode(',', $this->textColor);
        $style .= "color: rgb({$rgb}); ";
        
        switch ($align) {
            case 'C':
                $style .= "text-align: center; ";
                break;
            case 'R':
                $style .= "text-align: right; ";
                break;
            case 'L':
            default:
                $style .= "text-align: left; ";
                break;
        }
        
        $style .= "line-height: {$h}mm; vertical-align: middle; ";
        
        $this->pages[$this->currentPage] .= "<div style=\"{$style}\">" . htmlspecialchars($txt) . "</div>";
        
        if ($ln == 1) {
            $this->y += $h;
            $this->x = $this->margin;
        } else {
            $this->x += $w;
        }
    }
    
    public function Image($file, $x = '', $y = '', $w = 0, $h = 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false) {
        if (file_exists($file)) {
            $style = "position: absolute; left: {$x}mm; top: {$y}mm; width: {$w}mm; height: {$h}mm; ";
            $this->pages[$this->currentPage] .= "<img src=\"{$file}\" style=\"{$style}\" alt=\"Logo\">";
        }
    }
    
    public function Output($name = 'doc.pdf', $dest = 'I') {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Désignation d\'Arbitrage</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
        .page { position: relative; width: 297mm; height: 210mm; margin: 0 auto; background: white; }
        @media print {
            .page { page-break-after: always; }
        }
    </style>
</head>
<body>';
        
        foreach ($this->pages as $page) {
            $html .= '<div class="page">' . $page . '</div>';
        }
        
        $html .= '</body></html>';
        
        if ($dest == 'D') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $name . '"');
        } else {
            header('Content-Type: text/html; charset=utf-8');
        }
        
        echo $html;
    }
}
?> 