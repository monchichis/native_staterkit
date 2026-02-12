<?php
// Fungsi untuk mengubah byte ke format yang lebih mudah dibaca
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}
// Mendapatkan penggunaan memori saat ini
$currentMemoryUsage = memory_get_usage();

// Mendapatkan puncak penggunaan memori sepanjang waktu eksekusi skrip
$peakMemoryUsage = memory_get_peak_usage();

// Konversi ke dalam format yang lebih mudah dibaca
$currentMemoryUsageFormatted = formatBytes($currentMemoryUsage);
$peakMemoryUsageFormatted = formatBytes($peakMemoryUsage);
$memoryUsagePercentage = ($currentMemoryUsage / $peakMemoryUsage) * 100;
$memoryUsagePercentageFormatted = number_format($memoryUsagePercentage, 2);
// Tampilkan informasi
// echo "Current Memory Usage: $currentMemoryUsageFormatted<br>";
// echo "Peak Memory Usage: $peakMemoryUsageFormatted<br>";

function get_browser_name() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) {
        return 'Opera';
    } elseif (strpos($user_agent, 'Edg')) {
        return 'Microsoft Edge';
    } elseif (strpos($user_agent, 'Chrome')) {
        return 'Google Chrome';
    } elseif (strpos($user_agent, 'Safari')) {
        return 'Safari';
    } elseif (strpos($user_agent, 'Firefox')) {
        return 'Mozilla Firefox';
    } elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) {
        return 'Internet Explorer';
    }

    return 'Other';
}

// Contoh penggunaan
$browser_name = get_browser_name();
// echo "Browser yang digunakan: $browser_name";
function get_ram_info() {
    $os = strtolower(php_uname('s'));

    if (strpos($os, 'linux') !== false) {
        // Jika sistem operasi adalah Linux
        $mem_info = shell_exec('free -m | grep Mem');
        if (!$mem_info) {
            return 'Failed to retrieve memory information';
        }
        list($total, $used, $free, $shared, $buffers, $cached) = preg_split('/\s+/', $mem_info);

        $total_ram_in_kb = $total * 1024;
        $used_ram_in_kb = ($total - $free - $buffers - $cached) * 1024;
    } elseif (strpos($os, 'windows') !== false) {
        // Jika sistem operasi adalah Windows
        $total_ram_in_kb = shell_exec('wmic ComputerSystem get TotalPhysicalMemory | findstr [0-9]');
        $free_ram_in_kb = shell_exec('wmic OS get FreePhysicalMemory /Value');
        if (!$total_ram_in_kb || !$free_ram_in_kb) {
            return 'Failed to retrieve memory information';
        }

        // Membersihkan nilai dari karakter non-digit
        $total_ram_in_kb = preg_replace('/\D/', '', $total_ram_in_kb);
        $free_ram_in_kb = preg_replace('/\D/', '', $free_ram_in_kb);

        $used_ram_in_kb = $total_ram_in_kb - $free_ram_in_kb;
    } else {
        // Sistem operasi lainnya
        return 'Unknown';
    }

    // Menghitung persentase penggunaan RAM
    $percentage_used = ($used_ram_in_kb / $total_ram_in_kb) * 100;

    // Konversi kilobyte ke gigabyte
    $total_ram_in_gb = number_format($total_ram_in_kb / (1024 * 1024), 2);
    $used_ram_in_gb = number_format($used_ram_in_kb / (1024 * 1024), 2);

    return [
        'total_ram' => $total_ram_in_gb . ' GB',
        'used_ram' => $used_ram_in_gb . ' GB',
        'percentage_used' => $percentage_used
    ];
}

$ram_info = get_ram_info();
?>


