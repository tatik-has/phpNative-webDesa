<?php

$baseDir = __DIR__;

$categories = [
    'Controllers'  => ['path' => 'logic_tier/controllers',  'ext' => 'php'],
    'Services'     => ['path' => 'logic_tier/services',     'ext' => 'php'],
    'Repositories' => ['path' => 'data_tier/repositories',  'ext' => 'php'],
    'Config'       => ['path' => 'data_tier/config',        'ext' => 'php'],
    'Views'        => ['path' => 'presentation_tier',       'ext' => 'php'],
    'CSS'          => ['path' => 'presentation_tier/css',   'ext' => 'css'],
];

function scanFiles(string $dir, string $ext): array
{
    $files = [];
    if (!is_dir($dir)) return $files;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->getExtension() === $ext) {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

function countLines(string $filePath): int
{
    return count(file($filePath));
}

function countMethods(string $filePath): int
{
    $content = file_get_contents($filePath);
    preg_match_all('/^\s*(public|private|protected)\s+function\s+/m', $content, $matches);
    return count($matches[0]);
}

function countClasses(string $filePath): int
{
    $content = file_get_contents($filePath);
    preg_match_all('/^\s*(class|interface|trait)\s+/m', $content, $matches);
    return count($matches[0]);
}

function getStatus(string $category, int $files, int $methods): string
{
    if ($files === 0) return '-';
    $avg = $methods / $files;
    if ($category === 'Controllers') {
        if ($avg <= 5)  return 'RINGAN ✓';
        if ($avg <= 8)  return 'NORMAL ✓';
        return 'OVERLOAD !';
    }
    if ($category === 'Services') {
        if ($avg <= 8)  return 'RINGAN ✓';
        if ($avg <= 12) return 'NORMAL ✓';
        return 'BESAR !';
    }
    return 'OK ✓';
}

echo "\n";
echo str_repeat('=', 85) . "\n";
echo "  PHP NATIVE PROJECT STATS — MULTI-TIER ARCHITECTURE ANALYSIS\n";
echo "  " . $baseDir . "\n";
echo str_repeat('=', 85) . "\n";

printf(
    "| %-16s | %5s | %7s | %7s | %6s | %7s | %10s |\n",
    'Category', 'Files', 'Classes', 'Methods', 'Lines', 'Avg/File', 'Status'
);
echo str_repeat('-', 85) . "\n";

$totalFiles = $totalClasses = $totalMethods = $totalLines = 0;
$results = [];

foreach ($categories as $name => $config) {
    $dir   = $baseDir . '/' . $config['path'];
    $files = scanFiles($dir, $config['ext']);

    $classes = $methods = $lines = 0;
    foreach ($files as $file) {
        $lines += countLines($file);
        if ($config['ext'] === 'php') {
            $classes += countClasses($file);
            $methods += countMethods($file);
        }
    }

    $fileCount = count($files);
    $avg       = $fileCount > 0 ? round($methods / $fileCount, 1) : 0;
    $status    = getStatus($name, $fileCount, $methods);

    printf(
        "| %-16s | %5d | %7d | %7d | %6d | %7s | %10s |\n",
        $name, $fileCount, $classes, $methods, $lines,
        $config['ext'] === 'php' ? $avg : '-',
        $status
    );

    $totalFiles   += $fileCount;
    $totalClasses += $classes;
    $totalMethods += $methods;
    $totalLines   += $lines;

    $results[$name] = ['files' => $fileCount, 'methods' => $methods, 'avg' => $avg];
}

echo str_repeat('-', 85) . "\n";
printf(
    "| %-16s | %5d | %7d | %7d | %6d | %7s | %10s |\n",
    'TOTAL', $totalFiles, $totalClasses, $totalMethods, $totalLines, '-', '-'
);
echo str_repeat('=', 85) . "\n";

// =========================================================
//  ANALISIS OTOMATIS
// =========================================================

echo "\n";
echo str_repeat('=', 85) . "\n";
echo "  ANALISIS MULTI-TIER ARCHITECTURE\n";
echo str_repeat('=', 85) . "\n";

$ctrlAvg  = $results['Controllers']['avg'];
$svcAvg   = $results['Services']['avg'];
$repoFiles = $results['Repositories']['files'];

// 1. Controller tidak overload?
echo "\n[1] CONTROLLER LOAD CHECK\n";
echo "    Rata-rata method per controller : {$ctrlAvg} method/controller\n";
if ($ctrlAvg <= 5) {
    echo "    Status : RINGAN - Controller tidak overload.\n";
    echo "    Artinya : Setiap controller hanya menangani sedikit aksi,\n";
    echo "              logika bisnis sudah didelegasikan ke Service layer.\n";
} elseif ($ctrlAvg <= 8) {
    echo "    Status : NORMAL - Controller masih dalam batas wajar.\n";
} else {
    echo "    Status : OVERLOAD - Pertimbangkan memecah controller.\n";
}

// 2. Apakah Service layer aktif dipakai?
echo "\n[2] SERVICE LAYER CHECK\n";
$svcFiles = $results['Services']['files'];
echo "    Jumlah Service  : {$svcFiles} files\n";
echo "    Jumlah Controller : {$results['Controllers']['files']} files\n";
$ratio = $svcFiles > 0 ? round($results['Controllers']['files'] / $svcFiles, 2) : 0;
echo "    Rasio Controller:Service = 1:{$ratio}\n";
if ($svcFiles >= $results['Controllers']['files'] * 0.5) {
    echo "    Status : BAIK - Business logic sudah dipisah ke Service.\n";
} else {
    echo "    Status : PERLU REVIEW - Service layer kurang dimanfaatkan.\n";
}

// 3. Repository pattern?
echo "\n[3] REPOSITORY LAYER CHECK\n";
echo "    Jumlah Repository : {$repoFiles} files\n";
if ($repoFiles >= 3) {
    echo "    Status : BAIK - Data access sudah dipisah via Repository.\n";
    echo "    Artinya : Controller/Service tidak query langsung ke DB.\n";
} else {
    echo "    Status : PERLU REVIEW - Repository kurang lengkap.\n";
}

// 4. Kesimpulan
echo "\n[4] KESIMPULAN\n";
echo str_repeat('-', 85) . "\n";
echo "    Proyek ini menerapkan 3-Tier Architecture:\n";
echo "    - Presentation Tier : {$results['Views']['files']} Views + {$results['CSS']['files']} CSS files\n";
echo "    - Logic Tier        : {$results['Controllers']['files']} Controllers + {$results['Services']['files']} Services\n";
echo "    - Data Tier         : {$results['Repositories']['files']} Repositories + {$results['Config']['files']} Config\n\n";

if ($ctrlAvg <= 8 && $svcFiles >= 5 && $repoFiles >= 3) {
    echo "    HASIL : ARSITEKTUR MULTI-TIER BERHASIL DITERAPKAN\n";
    echo "    Controller ringan karena logika didelegasikan ke Service.\n";
    echo "    Data access terpisah melalui Repository pattern.\n";
} else {
    echo "    HASIL : ARSITEKTUR PERLU PENYESUAIAN\n";
}

echo str_repeat('=', 85) . "\n\n";