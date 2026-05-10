<?php
/**
 * PDF des instructions d'une recette (QR code) — mise en page lisible, accents français.
 */
declare(strict_types=1);

require_once __DIR__ . '/../controller/RecetteController.php';
require_once __DIR__ . '/../controller/InstructionController.php';
require_once __DIR__ . '/../lib/fpdf.php';

/** Couleurs (RGB 0–255) */
final class PdfRecipeTheme
{
    public const ACCENT_R = 15;
    public const ACCENT_G = 118;
    public const ACCENT_B = 110;

    public const TEXT_R = 30;
    public const TEXT_G = 41;
    public const TEXT_B = 59;

    public const MUTED_R = 100;
    public const MUTED_G = 116;
    public const MUTED_B = 139;

    public const BOX_BG_R = 248;
    public const BOX_BG_G = 250;
    public const BOX_BG_B = 252;

    public const BOX_BORDER_R = 226;
    public const BOX_BORDER_G = 232;
    public const BOX_BORDER_B = 240;
}

final class InstructionPDF extends FPDF
{
    public string $footerLeft = '';

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4');
        $this->SetMargins(16, 16, 16);
        $this->SetAutoPageBreak(true, 22);
        $this->AliasNbPages();
    }

    public function getLeftMargin(): float
    {
        return $this->lMargin;
    }

    public function getRightMargin(): float
    {
        return $this->rMargin;
    }

    public function Footer(): void
    {
        $this->SetY(-16);
        $this->SetDrawColor(
            PdfRecipeTheme::BOX_BORDER_R,
            PdfRecipeTheme::BOX_BORDER_G,
            PdfRecipeTheme::BOX_BORDER_B
        );
        $this->SetLineWidth(0.2);
        $this->Line($this->lMargin, $this->GetY(), $this->GetPageWidth() - $this->rMargin, $this->GetY());
        $this->Ln(2);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(
            PdfRecipeTheme::MUTED_R,
            PdfRecipeTheme::MUTED_G,
            PdfRecipeTheme::MUTED_B
        );
        $left = $this->footerLeft !== '' ? pdf_encode($this->footerLeft) : '';
        $this->Cell(0, 4, $left, 0, 0, 'L');
        $this->Cell(0, 4, pdf_encode('Page ') . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }
}

/**
 * Remplace symboles non gérés par les polices standard, puis encode pour WinAnsi (PDF).
 */
function normalize_for_pdf(string $text): string
{
    $repl = [
        "\u{2605}" => '*',
        "\u{2606}" => '*',
        "\u{2B50}" => '*',
        "\u{272F}" => '*',
        '★' => '*',
        '☆' => '*',
        '⭐' => '*',
        '✓' => '+',
        '–' => '-',
        '—' => '-',
        "\u{00A0}" => ' ',
    ];
    $text = strtr($text, $repl);
    return $text;
}

function pdf_encode(string $text): string
{
    $text = normalize_for_pdf($text);
    if (function_exists('iconv')) {
        $conv = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        if ($conv !== false && $conv !== '') {
            return $conv;
        }
    }
    if (function_exists('mb_convert_encoding')) {
        $fallback = @mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
        if ($fallback !== false) {
            return $fallback;
        }
    }
    return normalize_for_pdf(preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/u', '', $text) ?? '');
}

function project_root_path(): string
{
    return dirname(__DIR__);
}

/** @return string|null chemin fichier image local */
function resolve_recipe_image_path(string $webPath): ?string
{
    $webPath = trim($webPath);
    if ($webPath === '' || preg_match('#^https?://#i', $webPath) === 1) {
        return null;
    }
    $root = project_root_path();
    $candidates = [];
    if (str_starts_with($webPath, '/recette/')) {
        $candidates[] = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, substr($webPath, strlen('/recette/')));
    }
    $candidates[] = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($webPath, '/\\'));

    foreach ($candidates as $full) {
        if (is_file($full)) {
            return $full;
        }
    }
    return null;
}

function image_display_height_mm(string $path, float $widthMm): float
{
    $info = @getimagesize($path);
    if ($info === false || ($info[0] ?? 0) <= 0) {
        return $widthMm * 0.75;
    }
    return $widthMm * ($info[1] / $info[0]);
}

function safe_pdf_filename(string $nomRecette, int $recetteId): string
{
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nomRecette);
    if ($ascii === false) {
        $ascii = 'recette';
    }
    $slug = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $ascii) ?? 'recette';
    $slug = trim($slug, '-');
    if ($slug === '') {
        $slug = 'recette';
    }
    if (strlen($slug) > 40) {
        $slug = substr($slug, 0, 40);
    }
    return 'instructions-' . $recetteId . '-' . $slug . '.pdf';
}

/**
 * Bloc récapitulatif : hauteur auto (évite texte hors cadre).
 *
 * @param array{label:string,value:string} $rows
 */
function pdf_draw_meta_block(InstructionPDF $pdf, array $rows, float $w): void
{
    $lines = [];
    foreach ($rows as $row) {
        $lines[] = $row['label'] . ' : ' . $row['value'];
    }
    $text = implode("\n", $lines);

    $pdf->SetTextColor(
        PdfRecipeTheme::TEXT_R,
        PdfRecipeTheme::TEXT_G,
        PdfRecipeTheme::TEXT_B
    );
    $pdf->SetFillColor(
        PdfRecipeTheme::BOX_BG_R,
        PdfRecipeTheme::BOX_BG_G,
        PdfRecipeTheme::BOX_BG_B
    );
    $pdf->SetDrawColor(
        PdfRecipeTheme::BOX_BORDER_R,
        PdfRecipeTheme::BOX_BORDER_G,
        PdfRecipeTheme::BOX_BORDER_B
    );
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->MultiCell($w, 6.5, pdf_encode($text), 'LTRB', 'L', true);
    $pdf->Ln(5);
}

$recetteId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($recetteId <= 0) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Identifiant recette invalide.';
    exit;
}

$recetteCtl = new RecetteController();
$instructionCtl = new InstructionController();
$recette = $recetteCtl->getRecetteById($recetteId);
if ($recette === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Recette introuvable.';
    exit;
}

$instruction = $instructionCtl->getByRecetteId($recetteId);
if ($instruction === null) {
    $instructionCtl->syncFromRecette($recette);
    $instruction = $instructionCtl->getByRecetteId($recetteId);
}

$nom = (string) ($recette['nom'] ?? 'Recette');
$type = (string) ($recette['type'] ?? '');
$calories = (string) ($recette['calories'] ?? '0');
$temps = (string) ($recette['tempsPreparation'] ?? '0');
$difficulte = (string) ($recette['difficulte'] ?? '');
$impact = (string) ($recette['impactCarbone'] ?? '');
$imgWeb = (string) ($recette['image'] ?? '');

$ingredients = $instruction !== null ? trim((string) ($instruction['ingredients'] ?? '')) : '';
$preparation = $instruction !== null ? trim((string) ($instruction['preparation'] ?? '')) : '';
$ne = $instruction !== null ? (int) ($instruction['nombreEtapes'] ?? 0) : 0;
$instrTemps = $instruction !== null ? (int) ($instruction['temps'] ?? 0) : 0;

if ($ingredients === '') {
    $ingredients = '-';
}
if ($preparation === '') {
    $preparation = 'Aucune fiche instruction pour le moment.';
}

$pdf = new InstructionPDF();
$pdf->footerLeft = 'Recette : ' . $nom;

$pdf->SetTitle('Instructions — ' . $nom, true);
$pdf->AddPage();

/* Bandeau */
$pdf->SetFillColor(
    PdfRecipeTheme::ACCENT_R,
    PdfRecipeTheme::ACCENT_G,
    PdfRecipeTheme::ACCENT_B
);
$bannerH = 26;
$pdf->Rect(0, 0, $pdf->GetPageWidth(), $bannerH, 'F');
$pdf->SetTextColor(255, 255, 255);
$pdf->SetY(7);
$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(0, 5, pdf_encode('FICHE RECETTE'), 0, 1, 'C');
$pdf->SetFont('Helvetica', 'B', 17);
$pdf->Cell(0, 8, pdf_encode($nom), 0, 1, 'C');
$pdf->SetTextColor(
    PdfRecipeTheme::TEXT_R,
    PdfRecipeTheme::TEXT_G,
    PdfRecipeTheme::TEXT_B
);
$pdf->SetY($bannerH + 7);

$contentW = $pdf->GetPageWidth() - $pdf->getLeftMargin() - $pdf->getRightMargin();

$rows = [];
if ($type !== '') {
    $rows[] = ['label' => 'Type', 'value' => $type];
}
$rows[] = ['label' => 'Temps', 'value' => $temps . ' min'];
if ($difficulte !== '') {
    $rows[] = ['label' => 'Difficulté', 'value' => $difficulte];
}
$rows[] = ['label' => 'Calories', 'value' => $calories . ' kcal'];
if ($impact !== '') {
    $rows[] = ['label' => 'Impact carbone', 'value' => $impact];
}

$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(0, 6, pdf_encode('En résumé'), 0, 1, 'L');
$pdf->Ln(1);
pdf_draw_meta_block($pdf, $rows, $contentW);

$imgFs = resolve_recipe_image_path($imgWeb);
if ($imgFs !== null) {
    $imgW = 64;
    $ix = $pdf->getLeftMargin() + ($contentW - $imgW) / 2;
    $iy = $pdf->GetY();
    try {
        $pdf->Image($imgFs, $ix, $iy, $imgW);
        $ih = image_display_height_mm($imgFs, $imgW);
        $pdf->SetY($iy + $ih + 6);
    } catch (Throwable $e) {
        $pdf->Ln(4);
    }
}

/* Sections */
$pdf->Ln(2);
$pdf->SetFillColor(
    PdfRecipeTheme::ACCENT_R,
    PdfRecipeTheme::ACCENT_G,
    PdfRecipeTheme::ACCENT_B
);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->Cell(0, 8, '  ' . pdf_encode('Ingrédients'), 0, 1, 'L', true);

$pdf->SetTextColor(
    PdfRecipeTheme::TEXT_R,
    PdfRecipeTheme::TEXT_G,
    PdfRecipeTheme::TEXT_B
);
$pdf->SetFillColor(
    PdfRecipeTheme::BOX_BG_R,
    PdfRecipeTheme::BOX_BG_G,
    PdfRecipeTheme::BOX_BG_B
);
$pdf->SetDrawColor(
    PdfRecipeTheme::BOX_BORDER_R,
    PdfRecipeTheme::BOX_BORDER_G,
    PdfRecipeTheme::BOX_BORDER_B
);
$pdf->SetFont('Helvetica', '', 10.5);
$pdf->MultiCell(0, 6, pdf_encode($ingredients), 'LRB', 'L', true);
$pdf->Ln(5);

$pdf->SetFillColor(
    PdfRecipeTheme::ACCENT_R,
    PdfRecipeTheme::ACCENT_G,
    PdfRecipeTheme::ACCENT_B
);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->Cell(0, 8, '  ' . pdf_encode('Préparation (étapes)'), 0, 1, 'L', true);

$pdf->SetTextColor(
    PdfRecipeTheme::TEXT_R,
    PdfRecipeTheme::TEXT_G,
    PdfRecipeTheme::TEXT_B
);
$pdf->SetFillColor(
    PdfRecipeTheme::BOX_BG_R,
    PdfRecipeTheme::BOX_BG_G,
    PdfRecipeTheme::BOX_BG_B
);
$pdf->SetFont('Helvetica', '', 10.5);
$pdf->MultiCell(0, 6, pdf_encode($preparation), 'LRB', 'L', true);

if ($instruction !== null && ($ne > 0 || $instrTemps > 0)) {
    $pdf->Ln(4);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(
        PdfRecipeTheme::MUTED_R,
        PdfRecipeTheme::MUTED_G,
        PdfRecipeTheme::MUTED_B
    );
    $foot = '';
    if ($ne > 0) {
        $foot = $ne . ' étapes';
    }
    if ($instrTemps > 0) {
        $foot .= ($foot !== '' ? ' · ' : '') . $instrTemps . ' min (fiche instruction)';
    }
    $pdf->MultiCell(0, 5, pdf_encode($foot), 0, 'L');
}

$filename = safe_pdf_filename($nom, $recetteId);
$pdf->Output('I', $filename, true);
