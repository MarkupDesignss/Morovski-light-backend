<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $company_name ?? 'Catalogue' }} — {{ date('Y') }} Collection</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;900&family=Cinzel+Decorative:wght@700;900&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,500;1,700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">lig

<style>
  /* ============================================================
     RESET & BASE
     ============================================================ */
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }

  /* ============================================================
     ROYAL PALETTE
     ============================================================ */
  body {
    background: #cfc4a6;
    font-family: 'Cormorant Garamond', 'Georgia', 'Times New Roman', serif;
    color: #251a12;
    margin: 0;
    padding: 20px 0;
    -webkit-font-smoothing: antialiased;
  }

  /* ============================================================
     PAGE CONTAINER
     ============================================================ */
  .page {
    width: 210mm;
    height: 297mm;
    margin: 0 auto 24px auto;
    position: relative;
    overflow: hidden;          /* clip anything that might spill */
    background: #efe6d3;
    /*background-image: radial-gradient(ellipse at 50% 0%, rgba(182,144,63,0.10) 0%, transparent 55%);*/
    page-break-after: always;
    box-shadow: 0 12px 48px rgba(0,0,0,0.22);
  }

  .page.dark {
    background: #090c13;
    /*background-image: radial-gradient(ellipse at 50% 0%, rgba(238,205,130,0.06) 0%, transparent 55%);*/
    color: #faf5e7;
  }

  /* The last page must NOT force a page break */
  .last-page {
    page-break-after: auto !important;
    margin-bottom: 0 !important;
  }

  /* ============================================================
     FRAME / BORDER — double gilt rule with a crest mark
     ============================================================ */
  .frame {
    position: absolute;
    top: 8mm;
    left: 8mm;
    right: 8mm;
    bottom: 8mm;
    border: 1.5px solid #b6903f;
    pointer-events: none;
  }

  .frame::after {
    content: "";
    position: absolute;
    top: 5px;
    left: 5px;
    right: 5px;
    bottom: 5px;
    border: 1px solid #b6903f;
    opacity: 0.4;
  }

  .frame::before {
    content: "";
    position: absolute;
    top: -1px; left: -1px; right: -1px; bottom: -1px;
    /*background:*/
    /*  linear-gradient(#b6903f,#b6903f) top left / 26px 2.5px no-repeat,*/
    /*  linear-gradient(#b6903f,#b6903f) top left / 2.5px 26px no-repeat,*/
    /*  linear-gradient(#b6903f,#b6903f) top right / 26px 2.5px no-repeat,*/
    /*  linear-gradient(#b6903f,#b6903f) top right / 2.5px 26px no-repeat,*/
    /*  linear-gradient(#b6903f,#b6903f) bottom left / 26px 2.5px no-repeat,*/
    /*  linear-gradient(#b6903f,#b6903f) bottom left / 2.5px 26px no-repeat,*/
    /*  linear-gradient(#b6903f,#b6903f) bottom right / 26px 2.5px no-repeat,*/
    /*  linear-gradient(#b6903f,#b6903f) bottom right / 2.5px 26px no-repeat;*/
  }

  .page.dark .frame { border-color: #eecd82; }
  .page.dark .frame::after { border-color: #eecd82; }
  .page.dark .frame::before {
    background:
      linear-gradient(#eecd82,#eecd82) top left / 26px 2.5px no-repeat,
      linear-gradient(#eecd82,#eecd82) top left / 2.5px 26px no-repeat,
      linear-gradient(#eecd82,#eecd82) top right / 26px 2.5px no-repeat,
      linear-gradient(#eecd82,#eecd82) top right / 2.5px 26px no-repeat,
      linear-gradient(#eecd82,#eecd82) bottom left / 26px 2.5px no-repeat,
      linear-gradient(#eecd82,#eecd82) bottom left / 2.5px 26px no-repeat,
      linear-gradient(#eecd82,#eecd82) bottom right / 26px 2.5px no-repeat,
      linear-gradient(#eecd82,#eecd82) bottom right / 2.5px 26px no-repeat;
  }

  .crest-mark {
    position: absolute;
    top: 8mm;
    left: 50%;
    margin-left: -7.5px;
    margin-top: -7.5px;
    width: 15px;
    height: 15px;
    background: #efe6d3;
    border: 1.5px solid #b6903f;
    transform: rotate(45deg);
    z-index: 3;
  }
  .page.dark .crest-mark { background: #090c13; border-color: #eecd82; }
  .crest-mark::after {
    content: "";
    position: absolute;
    top: 5px; left: 5px;
    width: 5px; height: 5px;
    background: #6d1220;
  }

  /* ============================================================
     TYPOGRAPHY
     ============================================================ */
  .eyebrow {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    font-size: 10px;
    letter-spacing: .34em;
    text-transform: uppercase;
    color: #b6903f;
  }

  .page.dark .eyebrow { color: #eecd82; }

  .rule {
    height: 1.5px;
    background: #b6903f;
    width: 100%;
    margin-top: 10px;
  }

  .page.dark .rule { background: #eecd82; }

  .ornate-rule {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .ornate-rule .l { flex: 1; height: 1px; background: linear-gradient(90deg, transparent, #b6903f); }
  .ornate-rule .l.r { background: linear-gradient(90deg, #b6903f, transparent); }
  .ornate-rule .d { width: 6px; height: 6px; background: #b6903f; transform: rotate(45deg); flex-shrink:0; }

  h1, h2, h3 {
    font-family: 'Playfair Display', 'Georgia', 'Times New Roman', serif;
    letter-spacing: .01em;
    font-weight: 700;
  }

  /* ============================================================
     COVER PAGE — image only
     ============================================================ */
  .cover {
    width: 100%;
    height: 100%;
    position: relative;
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
  }

  /* ============================================================
     FOREWORD
     ============================================================ */
  .foreword {
    padding: 28mm 22mm 20mm 22mm;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .foreword .eyebrow { margin-bottom: 6px; }

  .foreword h2 {
    font-size: 38px;
    margin: 14px 0 26px 0;
    color: #251a12;
    line-height: 1.25;
    position: relative;
    padding-bottom: 18px;
  }

  .foreword h2::after {
    content: "";
    position: absolute;
    left: 0; bottom: 0;
    width: 54px;
    height: 3px;
    background: #b6903f;
  }

  .foreword p {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px;
    line-height: 1.85;
    color: #6d5a3f;
    margin-bottom: 16px;
    max-width: 95%;
  }

  .foreword p.lead::first-letter {
    font-family: 'Cinzel Decorative', serif;
    font-size: 54px;
    font-weight: 700;
    float: left;
    line-height: 0.8;
    padding: 6px 8px 0 0;
    color: #6d1220;
  }

  .foreword .signature {
    margin-top: 18px;
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-weight: 600;
    font-size: 19px;
    color: #251a12;
  }

  .foreword .stat-row {
    margin-top: 22px;
    display: flex;
    gap: 50px;
    padding-top: 22px;
  }

  .foreword .stat { display: flex; flex-direction: column; }

  .foreword .stat b {
    font-family: 'Playfair Display', serif;
    font-size: 30px;
    color: #6d1220;
    font-weight: 700;
  }

  .foreword .stat span {
    font-family: 'Cinzel', sans-serif;
    font-size: 9px;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: #6d5a3f;
    margin-top: 5px;
  }

  /* ============================================================
     TABLE OF CONTENTS
     ============================================================ */
  .toc {
    padding: 28mm 22mm 20mm 22mm;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .toc .eyebrow { margin-bottom: 6px; }
  .toc h2 { font-size: 36px; margin-bottom: 8px; }

  .toc .sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: #6d5a3f;
    font-size: 17px;
    margin-bottom: 34px;
  }

  .toc-list { list-style: none; margin-top: 6px; padding: 0; }

  .toc-list li {
    display: flex;
    align-items: center;
    padding: 16px 4px;
    border-bottom: 1px solid #d9c9a0;
    gap: 14px;
  }

  .toc-list li:last-child { border-bottom: none; }

  .toc-list .dot {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    flex-shrink: 0;
    display: table-cell;
    text-align: center;
    vertical-align: middle;
    border: 1px solid rgba(182,144,63,0.5);
  }
  .toc-list .dot-fill {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
  }

  .toc-list .name {
    font-family: 'Playfair Display', serif;
    font-size: 16px;
    letter-spacing: .01em;
    font-weight: 600;
    flex-shrink: 0;
  }

  .toc-list .leader {
    flex: 1;
    border-bottom: 1.5px dotted #c4b076;
    margin: 0 8px;
    height: 1px;
  }

  .toc-list .desc {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
    color: #6d5a3f;
    flex-shrink: 0;
  }

  .toc-list .num {
    font-family: 'Cinzel', serif;
    font-size: 14px;
    color: #b6903f;
    font-weight: 700;
    flex-shrink: 0;
  }

  /* ============================================================
     CATEGORY PAGE HEADER
     ============================================================ */
  .cat-page {
    padding: 12mm 10mm 8mm 10mm;
    height: 100%;
    overflow: hidden;          /* prevent any overflow from leaving the page */
    display: flex;
    flex-direction: column;
  }

  .cat-head {
    padding: 0 0 5mm 0;
    flex-shrink: 0;
    margin-bottom: 4mm;
    position: relative;
  }

  .cat-head .num {
    font-family: 'Cinzel', serif;
    font-size: 10px;
    letter-spacing: .26em;
    font-weight: 600;
    color: #b6903f;
    text-transform: uppercase;
  }

  .cat-head h2 {
    font-size: 34px;
    margin-top: 4px;
    letter-spacing: .01em;
    font-weight: 700;
  }

  .cat-head .desc {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 15px;
    color: #6d5a3f;
    margin-top: 3px;
  }

  .cat-head::after {
    content: "";
    position: absolute;
    left: 0; bottom: 0;
    width: 40px; height: 3px;
    background: #b6903f;
  }

  /* ============================================================
     PRODUCT GRID — float-based, with clearfix to avoid spilling
     ============================================================ */
  .product-grid {
    width: 100%;
    margin: 0;
    padding: 0;
    overflow: hidden;          /* contain all floated children */
    flex: 1;                   /* fill remaining height */
  }

  /* clearfix for the grid — prevents extra height from floats */
  .product-grid::after {
    content: "";
    display: table;
    clear: both;
  }

  .product-card {
    float: left;
    width: 25%;
    margin: 0;
    padding: 0;
    border: none;
    box-sizing: border-box;
  }

  .product-grid .product-card:nth-child(4n+1) {
    clear: left;
  }

  .card-inner {
    padding: 15px 12px 14px 12px;
    margin: 0;
    position: relative;
    background: #faf5e7;
    border-right: 1px solid #d9c9a0;
    border-bottom: 1px solid #d9c9a0;
    text-align: center;
    height: 250px;            /* fixed height – keeps rows uniform */
    overflow: hidden;         /* clip any overflow inside the card */
    box-sizing: border-box;
  }

  .product-grid .product-card:nth-child(4n+1) .card-inner {
    border-left: 1px solid #d9c9a0;
  }

  .product-grid .product-card:nth-child(-n+4) .card-inner {
    border-top: 1px solid #d9c9a0;
  }

  .card-inner::before,
  .card-inner::after {
    content: "";
    position: absolute;
    top: 7px;
    width: 9px;
    height: 9px;
    border-top: 1.5px solid #b6903f;
  }
  .card-inner::before { left: 7px; border-left: 1.5px solid #b6903f; }
  .card-inner::after  { right: 7px; border-right: 1.5px solid #b6903f; }

  .card-inner .plate-number {
    font-family: 'Cinzel', serif;
    font-size: 9px;
    font-weight: 600;
    letter-spacing: .12em;
    margin-bottom: 8px;
    text-transform: uppercase;
  }

  .card-inner .image-wrap {
    width: 100%;
    height: 106px;
    margin: 0 0 11px 0;
    background: #e7dcbc;
    overflow: hidden;
    position: relative;
    border-radius: 6px;
    border: 1px solid #b6903f;
    display: table;
  }

  .card-inner .image-wrap .img-cell {
    display: table-cell;
    width: 100%;
    height: 106px;
    vertical-align: middle;
    text-align: center;
  }

  .card-inner .image-wrap img {
    max-width: 100%;
    max-height: 104px;
    width: auto;
    height: auto;
    display: inline-block;
    vertical-align: middle;
  }

  .card-inner .image-placeholder {
    display: table-cell;
    width: 100%;
    height: 106px;
    vertical-align: middle;
    text-align: center;
    color: #b6903f;
    font-size: 22px;
    background: #e7dcbc;
  }

  .card-inner .product-name {
    font-family: 'Playfair Display', serif;
    font-size: 13.5px;
    font-weight: 700;
    letter-spacing: .01em;
    color: #251a12;
    line-height: 1.25;
    margin-bottom: 2px;
  }

  .card-inner .product-model {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: #6d5a3f;
    font-size: 11px;
    margin-bottom: 6px;
  }

  .card-inner .meta-row {
    font-family: 'Jost', sans-serif;
    font-size: 7.5px;
    letter-spacing: .05em;
    color: #8a7a5c;
    margin-bottom: 8px;
    text-transform: uppercase;
  }
  .card-inner .meta-row .sep { color: #cdbb8e; margin: 0 4px; }

  .card-inner .price-plate {
    border-top: 1px solid #b6903f;
    border-bottom: 1px solid #b6903f;
    padding: 5px 4px;
  }

  .card-inner .product-price {
    font-family: 'Playfair Display', serif;
    font-size: 14px;
    font-weight: 700;
    color: #6d1220;
  }

  .card-inner .product-price.request-price {
    font-family: 'Cormorant Garamond', serif;
    font-size: 10.5px;
    font-weight: 600;
    color: #6d5a3f;
    font-style: italic;
    letter-spacing: .04em;
  }

  .card-inner .badge {
    position: absolute;
    top: 5px;
    right: 7px;
    display: block;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: radial-gradient(circle at 35% 30%, #7d1a26, #470b14);
    color: #eecd82;
    font-family: 'Cinzel', sans-serif;
    font-size: 5.5px;
    font-weight: 700;
    letter-spacing: .03em;
    text-transform: uppercase;
    line-height: 1.05;
    text-align: center;
    padding-top: 7px;
    z-index: 4;
    box-shadow: 0 2px 6px rgba(0,0,0,0.45), 0 0 0 1.5px #eecd82, 0 0 0 3px rgba(71,11,20,0.2);
  }

  /* ============================================================
     BACK COVER
     ============================================================ */
  .back {
    text-align: center;
    position: relative;
    height: 100%;
    display: table;
    width: 100%;
  }

  .back-content {
    display: table-cell;
    vertical-align: middle;
    padding: 50px 60px;
  }

  .back .line1 {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-weight: 500;
    color: #eecd82;
    margin: 16px 0 32px 0;
    font-size: 18px;
    opacity: 0.95;
  }

  .back .contact {
    margin-top: 28px;
    font-family: 'Jost', sans-serif;
    font-size: 13px;
    letter-spacing: .04em;
    color: #faf5e7;
    line-height: 2.3;
    opacity: 0.88;
  }

  .back .contact b { color: #eecd82; font-weight: 600; font-family: 'Cinzel', serif; letter-spacing: .04em; }

  .back-logo {
    max-width: 250px;
    max-height: 120px;
    object-fit: contain;
    margin-bottom: 24px;
  }
.cover-logo {
  position: absolute;
  top: 14mm;
  right: 14mm;
  max-width: 90px;
  max-height: 50px;
  object-fit: contain;
  z-index: 5;
}
  .royal-seal {
    width: 84px;
    height: 84px;
    margin: 0 auto 20px auto;
    border-radius: 50%;
    border: 1.5px solid #eecd82;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: radial-gradient(circle at 50% 40%, rgba(238,205,130,0.16), transparent 70%);
  }
  .royal-seal::before {
    content: "";
    position: absolute;
    top: 6px; left: 6px; right: 6px; bottom: 6px;
    border: 1px solid #eecd82;
    border-radius: 50%;
    opacity: .55;
  }
  .royal-seal .initial {
    font-family: 'Cinzel Decorative', serif;
    font-size: 28px;
    font-weight: 700;
    color: #eecd82;
  }
  .royal-seal .seal-tick {
    position: absolute;
    width: 6px;
    height: 6px;
    background: #eecd82;
    transform: rotate(45deg);
  }
  .royal-seal .seal-tick.t { top: -9px; left: 50%; margin-left: -3px; }
  .royal-seal .seal-tick.b { bottom: -9px; left: 50%; margin-left: -3px; }
  .royal-seal .seal-tick.l { left: -9px; top: 50%; margin-top: -3px; }
  .royal-seal .seal-tick.r { right: -9px; top: 50%; margin-top: -3px; }

  /* ============================================================
     PRINT STYLES
     ============================================================ */
  @media print {
    body { background: #fff; margin: 0; padding: 0; }
    .page {
      margin: 0;
      box-shadow: none;
      page-break-after: always;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    .page.dark {
      background: #090c13 !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      color-adjust: exact !important;
    }
  }

  /* ============================================================
     SCREEN STYLES
     ============================================================ */
  @media screen {
    .page { box-shadow: 0 12px 48px rgba(0,0,0,0.22); }
    body { padding: 24px; background: #c3b795; }
  }

  /* ============================================================
     RESPONSIVE (screen preview only — PDF ignores these)
     ============================================================ */
  @media screen and (max-width: 900px) {
    .page {
      width: 100%;
      height: auto;
      min-height: 297mm;
    }
    .product-card {
      width: 50%;
    }
    .foreword .stat-row {
      flex-wrap: wrap;
      gap: 20px;
    }
  }

  @media screen and (max-width: 500px) {
    .product-card {
      width: 100%;
    }
    .card-inner {
      height: auto;
    }
    .card-inner .product-name {
      font-size: 13px;
    }
    .card-inner .product-price {
      font-size: 13px;
    }
  }
</style>
</head>
<body>

@php
    // ----- LOGO -----
    $logoPath = (isset($logoPath) && trim($logoPath) !== '') ? trim($logoPath) : 'https://www.markupdesigns.net/morovski-light/storage/logos/1Q7b394XXZoVpNvI8CmnpJlWUyHoGBHDqDgUVPMN.png';

    $logoExists = true;

    // ----- BACKGROUND IMAGE (cover) -----
    $bgImagePath = null;
    $bgImageExists = false;
    $bgPaths = [
        'public/logo/cata.jpg',
        'logo/cata.jpg',
        'cata.jpg',
    ];
    foreach ($bgPaths as $path) {
        if (file_exists(public_path($path))) {
            $bgImagePath = $path;
            $bgImageExists = true;
            break;
        }
    }

    // ----- DATA -----
    $categories = $categories ?? collect();
    $items = $items ?? collect();

    if ($items->isEmpty()) {
        $items = collect([
            (object)['name' => 'E816-1 WHITE', 'model' => 'White Finish', 'specifications' => ['diameter' => '0 cm', 'height' => '0 cm'], 'sku' => 'E816-1 WHITE', 'price' => 2400, 'category_id' => 4, 'category_name' => 'Table & Floor Lamps', 'best_seller' => false],
            (object)['name' => 'L6224-1', 'model' => 'Smoke Finish', 'specifications' => ['diameter' => '0 cm', 'height' => '0 cm'], 'sku' => 'L6224-1', 'price' => 1800, 'category_id' => 4, 'category_name' => 'Table & Floor Lamps', 'best_seller' => false],
            (object)['name' => 'E816-1 SMOKE', 'model' => 'Smoke Finish', 'specifications' => ['diameter' => '0 cm', 'height' => '0 cm'], 'sku' => 'E816-1 SMOKE', 'price' => 2400, 'category_id' => 4, 'category_name' => 'Table & Floor Lamps', 'best_seller' => false],
            (object)['name' => 'L6623-2 SMOKE', 'model' => 'Smoke Finish', 'specifications' => ['diameter' => '0 cm', 'height' => '0 cm'], 'sku' => 'L6623-2 SMOKE', 'price' => 2400, 'category_id' => 4, 'category_name' => 'Table & Floor Lamps', 'best_seller' => false],
            (object)['name' => 'D643-5', 'model' => 'Brass Finish', 'specifications' => ['diameter' => '0 cm', 'height' => '0 cm'], 'sku' => 'D643-5', 'price' => 7950, 'category_id' => 4, 'category_name' => 'Table & Floor Lamps', 'best_seller' => true],
            (object)['name' => '5109-D1000MM', 'model' => 'Jhoomar', 'specifications' => ['diameter' => '0 cm', 'height' => '10 cm'], 'sku' => '5109-D1000MM', 'price' => null, 'category_id' => 1, 'category_name' => 'Jhoomar', 'best_seller' => false],
            (object)['name' => '5109-D1000MM', 'model' => 'Jhoomar', 'specifications' => ['diameter' => '0 cm', 'height' => '10 cm'], 'sku' => '5109-D1000MM', 'price' => null, 'category_id' => 1, 'category_name' => 'Jhoomar', 'best_seller' => false],
            (object)['name' => '5109-L1200', 'model' => 'Jhoomar', 'specifications' => ['diameter' => '0 cm', 'height' => '10 cm'], 'sku' => '5109-L1200', 'price' => null, 'category_id' => 1, 'category_name' => 'Jhoomar', 'best_seller' => false],
            (object)['name' => '5109-D1000MM', 'model' => 'Jhoomar', 'specifications' => ['diameter' => '0 cm', 'height' => '10 cm'], 'sku' => '5109-D1000MM', 'price' => null, 'category_id' => 1, 'category_name' => 'Jhoomar', 'best_seller' => false],
            (object)['name' => '5109-D1000MM', 'model' => 'Jhoomar', 'specifications' => ['diameter' => '0 cm', 'height' => '10 cm'], 'sku' => '5109-D1000MM', 'price' => null, 'category_id' => 1, 'category_name' => 'Jhoomar', 'best_seller' => false],
            (object)['name' => '5109-L1200', 'model' => 'Jhoomar', 'specifications' => ['diameter' => '0 cm', 'height' => '10 cm'], 'sku' => '5109-L1200', 'price' => null, 'category_id' => 1, 'category_name' => 'Jhoomar', 'best_seller' => false],
        ]);
    }

    // Group items by category
    $groupedItems = $items->groupBy(function($item) {
        return $item->category_id ?? 'uncategorized';
    });

    // Category names
    $categoryNames = [];
    foreach ($groupedItems as $catId => $catItems) {
        if ($catId === 'uncategorized') {
            $categoryNames[$catId] = 'Uncategorized';
        } else {
            $category = $categories->firstWhere('id', $catId);
            if ($category && $category->name) {
                $categoryNames[$catId] = $category->name;
            } else {
                $firstItem = $catItems->first();
                if (isset($firstItem->category_name) && $firstItem->category_name) {
                    $categoryNames[$catId] = $firstItem->category_name;
                } else {
                    $defaultNames = [
                        1 => 'Jhoomar',
                        2 => 'Pendant Lights',
                        3 => 'Wall Lights',
                        4 => 'Table & Floor Lamps',
                        5 => 'Floor Lamps',
                        6 => 'Outdoor Lights',
                    ];
                    $categoryNames[$catId] = $defaultNames[$catId] ?? 'Category ' . $catId;
                }
            }
        }
    }

    $totalCategories = $groupedItems->count();
    $totalItems = $items->count();

    // Group items per category with max 8 per page (4 cols × 2 rows)
    $categoryPages = [];
    $pageNum = 4;
    $sectionNum = 1;

    foreach ($groupedItems as $catId => $catItems) {
        $categoryName = $categoryNames[$catId] ?? 'Uncategorized';
        $itemCount = $catItems->count();

        $itemsPerPage = 8;
        $pagesNeeded = ceil($itemCount / $itemsPerPage);

        $chunks = $catItems->chunk($itemsPerPage);
        $chunkIndex = 0;
        foreach ($chunks as $chunk) {
            $categoryPages[] = [
                'category_id' => $catId,
                'category_name' => $categoryName,
                'items' => $chunk->values(),
                'page_number' => $pageNum,
                'section_number' => $sectionNum,
                'is_first_page' => $chunkIndex === 0,
                'total_in_category' => $itemCount,
                'total_pages' => $pagesNeeded,
                'current_page' => $chunkIndex + 1,
            ];
            $pageNum++;
            $chunkIndex++;
        }
        $sectionNum++;
    }

    // Royal enamel palette for section colour-coding
    $categoryColors = [
        '1' => '#6d1220',
        '2' => '#163b2c',
        '3' => '#142449',
        '4' => '#3d1e46',
        '5' => '#8a5a2e',
        '6' => '#4a0e17',
        'uncategorized' => '#6d5a3f',
    ];

    $categorySummary = [];
    $colorIndex = 0;
    $colorKeys = ['#6d1220','#163b2c','#142449','#3d1e46','#8a5a2e','#4a0e17'];
    foreach ($groupedItems as $catId => $catItems) {
        $categorySummary[] = [
            'id' => $catId,
            'name' => $categoryNames[$catId] ?? 'Uncategorized',
            'count' => $catItems->count(),
            'color' => $categoryColors[$catId] ?? $colorKeys[$colorIndex % count($colorKeys)],
        ];
        $colorIndex++;
    }

    // Company data with defaults
    $companyName = $company_name ?? 'Morovski Light';
    $companyInitial = strtoupper(substr($companyName, 0, 1));

    $companyData = [
        'tagline' => 'Illuminate Every Room Like a Royal Chamber',
        'categories' => 'Chandeliers · Pendants · Wall · Table · Floor · Outdoor',
        'foreword_title' => 'Light, Crafted for a Crown',
        'foreword_text' => 'Every fixture in this collection is composed the way a court jeweller sets a stone — with proportion, patience, and an eye for how light will fall across a room for generations to come. Brass is hand-turned, crystal is cut in small batches, and every finish is aged to a warmth that only deepens with time, fit for a house that intends to be remembered.',
        'foreword_text_2' => 'This catalogue is arranged as six chambers of a single estate: Chandeliers for the room that commands attention, Pendants for the table that gathers a family, Wall Lights for the hallway that welcomes a guest, Table and Floor Lamps for quiet corners, and Outdoor Lights for the threshold that greets them all.',
        'signature' => 'The Design Atelier',
        'established' => 'EST.',
        'established_text' => 'Handcrafted Since 1998',
        'address' => '42 Heritage Court, Greater Noida, Uttar Pradesh, India',
        'email' => 'support@morovski.com',
        'phone' => '+91 98XXX XXXXX',
        'website' => 'www.morovskilight.in',
        'social' => '@Morvoski.lights'
    ];

    $showPrice = $showPrice ?? true;
@endphp

{{-- ============================================================
     COVER PAGE
     ============================================================ --}}
<section class="page dark cover" @if($bgImageExists && $bgImagePath) style="background-image: url('{{ asset($bgImagePath) }}');" @endif>
  <div class="frame"></div>
  @if($logoExists && $logoPath)
    <img class="cover-logo" src="{{ $logoPath }}" alt="{{ $companyName }}" />
  @endif
</section>

{{-- FOREWORD --}}
<section class="page">
  <div class="frame"></div>
  <div class="crest-mark"></div>
  <div class="foreword">
    <div class="eyebrow" style="color:#6d1220">The Collection</div>
    <h2>{{ $companyData['foreword_title'] }}</h2>
    <p class="lead">{{ $companyData['foreword_text'] }}</p>
    <p>{{ $companyData['foreword_text_2'] }}</p>
    <div class="signature">— {{ $companyData['signature'] }}</div>
    <div class="ornate-rule" style="margin-top:30px;">
      <span class="l"></span><span class="d"></span><span class="l r"></span>
    </div>
    <div class="stat-row">
      <div class="stat">
        <b>{{ str_pad($totalCategories, 2, '0', STR_PAD_LEFT) }}</b>
        <span>Collections</span>
      </div>
      <div class="stat">
        <b>{{ $totalItems }}</b>
        <span>Signature Pieces</span>
      </div>
      <div class="stat">
        <b>{{ $companyData['established'] }}</b>
        <span>{{ $companyData['established_text'] }}</span>
      </div>
    </div>
  </div>
</section>

{{-- TABLE OF CONTENTS --}}
<section class="page">
  <div class="frame"></div>
  <div class="crest-mark"></div>
  <div class="toc">
    <div class="eyebrow" style="color:#6d1220">Contents</div>
    <h2>Inside the Collection</h2>
    <div class="sub">{{ $totalCategories }} chambers of light, one house of craft</div>
    <div class="ornate-rule" style="margin-bottom:6px;"><span class="l"></span><span class="d"></span><span class="l r"></span></div>
    <ul class="toc-list">
      @foreach($categorySummary as $index => $cat)
      <li>
        <span class="dot"><span class="dot-fill" style="background:{{ $cat['color'] }};"></span></span>
        <span class="name">{{ $index + 1 }} — {{ $cat['name'] }}</span>
        <span class="leader"></span>
        <span class="desc">{{ $cat['count'] }} pieces</span>
        <span class="num">{{ str_pad($index + 4, 2, '0', STR_PAD_LEFT) }}</span>
      </li>
      @endforeach
    </ul>
  </div>
</section>

{{-- PRODUCT PAGES --}}
@foreach($categoryPages as $catPage)
<section class="page">
  <div class="frame"></div>
  <div class="crest-mark"></div>
  <div class="cat-page">
    <div class="cat-head">
      <div class="num" style="color:{{ $categoryColors[$catPage['category_id']] ?? '#b6903f' }}">
        SECTION {{ str_pad($catPage['section_number'], 2, '0', STR_PAD_LEFT) }} · PAGE {{ str_pad($catPage['page_number'], 2, '0', STR_PAD_LEFT) }}
        @if(isset($catPage['total_pages']) && $catPage['total_pages'] > 1)
          · ({{ $catPage['current_page'] }}/{{ $catPage['total_pages'] }})
        @endif
      </div>
      <h2>{{ $catPage['category_name'] }}</h2>
      <div class="desc">{{ $catPage['total_in_category'] }} pieces in this collection</div>
    </div>

    <div class="product-grid">
      @foreach($catPage['items'] as $item)
      <div class="product-card">
        <div class="card-inner">
          <div class="plate-number" style="color:{{ $categoryColors[$catPage['category_id']] ?? '#b6903f' }};">N&deg; {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>

          <div class="image-wrap">
            <div class="img-cell">
              @if(isset($item->images) && $item->images->isNotEmpty())
                <img src="{{ asset('storage/' . $item->images->first()->image) }}" alt="{{ $item->name }}">
              @else
                <span style="color:#b6903f;font-size:22px;">✦</span>
              @endif
            </div>
            @if(isset($item->best_seller) && $item->best_seller)
              <span class="badge">★<br>BEST</span>
            @endif
          </div>

          <div class="product-name">{{ $item->name }}</div>
          <!--<div class="product-model">{{ $item->model ?? '' }}</div>-->
          <div class="meta-row">
            Ø {{ $item->specifications['diameter'] ?? '—' }} · H {{ $item->specifications['height'] ?? '—' }}<span class="sep">|</span>{{ $item->sku ?? 'N/A' }}
          </div>
          <div class="price-plate" style="border-color:{{ $categoryColors[$catPage['category_id']] ?? '#b6903f' }};">
            <div class="product-price @if(!isset($item->price) || !$item->price || !$showPrice) request-price @endif">
              @if(isset($item->price) && $item->price && $showPrice)
                ₹ {{ number_format($item->price) }}
              @else
                Price on Request
              @endif
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    <!-- clearfix is applied via ::after in CSS -->
  </div>
</section>
@endforeach

{{-- ============================================================
     BACK COVER — last page, no page break after
     ============================================================ --}}
<section class="page dark back last-page">
  <div class="frame"></div>
  <div class="crest-mark"></div>
  <div class="back-content">
    @if($logoExists && $logoPath)
      <img class="back-logo" src="{{ $logoPath }}" alt="{{ $companyName }}" />
    @else
      <div class="royal-seal">
        <span class="seal-tick t"></span>
        <span class="seal-tick r"></span>
        <span class="seal-tick b"></span>
        <span class="seal-tick l"></span>
        <span class="initial">{{ $companyInitial }}</span>
      </div>
    @endif
    <div class="line1">{{ $companyData['tagline'] }}</div>
    <div class="contact">
      <b>Showroom</b> — {{ $companyData['address'] }}<br>
      <b>Trade &amp; Enquiries</b> — {{ $companyData['email'] }} · {{ $companyData['phone'] }}<br>
      <b>Online</b> — {{ $companyData['website'] }} · {{ $companyData['social'] }}
    </div>
  </div>
</section>

</body>
</html>