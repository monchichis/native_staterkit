<?php
/**
 * System Resources API
 * Returns comprehensive system information for neofetch-style display
 */

// Start session for authentication check
session_start();

// Check if user is logged in
if (!isset($_SESSION['level'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check if shell_exec is enabled
if (!function_exists('isShellEnabled')) {
    function isShellEnabled() {
        return function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))));
    }
}

// Format bytes to human readable (GiB style like neofetch)
if (!function_exists('formatBytesGiB')) {
    function formatBytesGiB($bytes, $precision = 2) {
        $gib = $bytes / (1024 * 1024 * 1024);
        return round($gib, $precision) . ' GiB';
    }
}

// Format bytes to human readable
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}

// Determine colors based on usage
if (!function_exists('getColor')) {
    function getColor($percent) {
        if ($percent < 50) return '#4caf50';
        if ($percent < 80) return '#ffeb3b';
        return '#f44336';
    }
}

// Initialize variables
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$systemInfo = [];

// Get Username and Hostname
$username = getenv('USERNAME') ?: getenv('USER') ?: 'user';
$hostname = gethostname() ?: 'localhost';
$systemInfo['user'] = $username;
$systemInfo['hostname'] = $hostname;
$systemInfo['header'] = $username . '@' . $hostname;

// Get OS
$os = PHP_OS;
if (isShellEnabled()) {
    if ($isWindows) {
        $wmicOS = @shell_exec('wmic os get Caption,OSArchitecture /value 2>&1');
        if (preg_match('/Caption=(.+)/i', $wmicOS, $osMatch)) {
            $os = trim($osMatch[1]);
        }
        if (preg_match('/OSArchitecture=(.+)/i', $wmicOS, $archMatch)) {
            $os .= ' [' . trim($archMatch[1]) . ']';
        }
    } else {
        $os = @shell_exec('uname -o 2>/dev/null') ?: PHP_OS;
        $os = trim($os);
    }
}
$systemInfo['os'] = $os;

// Get Host (Computer Manufacturer and Model)
$host = 'N/A';
if (isShellEnabled() && $isWindows) {
    $wmicHost = @shell_exec('wmic computersystem get Manufacturer,Model /value 2>&1');
    $manufacturer = '';
    $model = '';
    if (preg_match('/Manufacturer=(.+)/i', $wmicHost, $mfMatch)) {
        $manufacturer = trim($mfMatch[1]);
    }
    if (preg_match('/Model=(.+)/i', $wmicHost, $mdMatch)) {
        $model = trim($mdMatch[1]);
    }
    $host = trim($manufacturer . ' ' . $model);
}
$systemInfo['host'] = $host;

// Get Kernel
$kernel = php_uname('r');
if (isShellEnabled() && $isWindows) {
    $wmicKernel = @shell_exec('wmic os get Version /value 2>&1');
    if (preg_match('/Version=(.+)/i', $wmicKernel, $kMatch)) {
        $kernel = trim($kMatch[1]);
    }
}
$systemInfo['kernel'] = $kernel;

// Get Motherboard
$motherboard = 'N/A';
if (isShellEnabled() && $isWindows) {
    $wmicMB = @shell_exec('wmic baseboard get Manufacturer,Product /value 2>&1');
    $mbMfr = '';
    $mbProduct = '';
    if (preg_match('/Manufacturer=(.+)/i', $wmicMB, $mbMfrMatch)) {
        $mbMfr = trim($mbMfrMatch[1]);
    }
    if (preg_match('/Product=(.+)/i', $wmicMB, $mbProdMatch)) {
        $mbProduct = trim($mbProdMatch[1]);
    }
    $motherboard = trim($mbMfr . ' ' . $mbProduct);
}
$systemInfo['motherboard'] = $motherboard;

// Get Uptime
$uptime = 'N/A';
if (isShellEnabled()) {
    if ($isWindows) {
        $wmicUptime = @shell_exec('wmic os get LastBootUpTime /value 2>&1');
        if (preg_match('/LastBootUpTime=(\d{14})/i', $wmicUptime, $bootMatch)) {
            $bootTime = DateTime::createFromFormat('YmdHis', $bootMatch[1]);
            if ($bootTime) {
                $now = new DateTime();
                $diff = $now->diff($bootTime);
                $parts = [];
                if ($diff->d > 0) $parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
                if ($diff->h > 0) $parts[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
                if ($diff->i > 0) $parts[] = $diff->i . ' min' . ($diff->i > 1 ? 's' : '');
                $uptime = implode(', ', $parts) ?: '< 1 min';
            }
        }
    } else {
        $uptimeData = @shell_exec('uptime -p 2>/dev/null');
        if ($uptimeData) $uptime = trim(str_replace('up ', '', $uptimeData));
    }
}
$systemInfo['uptime'] = $uptime;

// Get Shell
$shell = 'N/A';
if ($isWindows) {
    $psVersion = @shell_exec('powershell "$PSVersionTable.PSVersion.ToString()" 2>&1');
    if ($psVersion && !preg_match('/error|not recognized/i', $psVersion)) {
        $shell = 'PowerShell v' . trim($psVersion);
    } else {
        $shell = 'CMD';
    }
} else {
    $shell = getenv('SHELL') ?: '/bin/bash';
}
$systemInfo['shell'] = $shell;

// Get Resolution
$resolution = 'N/A';
if (isShellEnabled() && $isWindows) {
    $wmicRes = @shell_exec('wmic path Win32_VideoController get CurrentHorizontalResolution,CurrentVerticalResolution /value 2>&1');
    if (preg_match('/CurrentHorizontalResolution=(\d+)/i', $wmicRes, $hRes) &&
        preg_match('/CurrentVerticalResolution=(\d+)/i', $wmicRes, $vRes)) {
        $resolution = $hRes[1] . 'x' . $vRes[1];
    }
}
$systemInfo['resolution'] = $resolution;

// Get Terminal
$terminal = 'N/A';
if ($isWindows) {
    $terminal = 'Windows Console';
    if (getenv('WT_SESSION')) {
        $terminal = 'Windows Terminal';
    }
} else {
    $terminal = getenv('TERM') ?: 'unknown';
}
$systemInfo['terminal'] = $terminal;

// Get CPU
$cpu = 'N/A';
if (isShellEnabled()) {
    if ($isWindows) {
        $wmicCPU = @shell_exec('wmic cpu get Name,MaxClockSpeed /value 2>&1');
        if (preg_match('/Name=(.+)/i', $wmicCPU, $cpuMatch)) {
            $cpu = trim($cpuMatch[1]);
        }
        if (preg_match('/MaxClockSpeed=(\d+)/i', $wmicCPU, $clockMatch)) {
            $ghz = round($clockMatch[1] / 1000, 3);
            $cpu .= ' @ ' . $ghz . 'GHz';
        }
    } else {
        $cpuData = @shell_exec('cat /proc/cpuinfo 2>/dev/null | grep "model name" | head -1');
        if ($cpuData && preg_match('/model name\s*:\s*(.+)/i', $cpuData, $cpuMatch)) {
            $cpu = trim($cpuMatch[1]);
        }
    }
}
$systemInfo['cpu'] = $cpu;

// Get GPU(s)
$gpus = [];
if (isShellEnabled() && $isWindows) {
    $wmicGPU = @shell_exec('wmic path Win32_VideoController get Name /value 2>&1');
    if (preg_match_all('/Name=(.+)/i', $wmicGPU, $gpuMatches)) {
        foreach ($gpuMatches[1] as $gpu) {
            $gpus[] = trim($gpu);
        }
    }
}
$systemInfo['gpu'] = $gpus;

// Get RAM info
$ramTotal = 0;
$ramUsed = 0;
$ramFree = 0;

if (isShellEnabled()) {
    if ($isWindows) {
        $totalMem = @shell_exec('wmic computersystem get TotalPhysicalMemory /value 2>&1');
        $freeMem = @shell_exec('wmic os get FreePhysicalMemory /value 2>&1');
        
        if (preg_match('/TotalPhysicalMemory=(\d+)/i', $totalMem, $totalMatch)) {
            $ramTotal = (int)$totalMatch[1];
        }
        if (preg_match('/FreePhysicalMemory=(\d+)/i', $freeMem, $freeMatch)) {
            $ramFree = (int)$freeMatch[1] * 1024;
            $ramUsed = $ramTotal - $ramFree;
        }
    } else {
        $memInfo = @shell_exec('cat /proc/meminfo 2>/dev/null');
        if (preg_match('/MemTotal:\s+(\d+)/i', $memInfo, $totalMatch)) {
            $ramTotal = (int)$totalMatch[1] * 1024;
        }
        if (preg_match('/MemAvailable:\s+(\d+)/i', $memInfo, $availMatch)) {
            $ramFree = (int)$availMatch[1] * 1024;
            $ramUsed = $ramTotal - $ramFree;
        }
    }
}
$ramPercent = $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100) : 0;
$systemInfo['memory'] = [
    'used' => $ramUsed,
    'total' => $ramTotal,
    'percent' => $ramPercent,
    'display' => formatBytesGiB($ramUsed) . ' / ' . formatBytesGiB($ramTotal) . ' (' . $ramPercent . '%)',
    'color' => getColor($ramPercent)
];

// Get Disk info
$diskTotal = @disk_total_space('/');
$diskFree = @disk_free_space('/');
$diskDrive = 'Disk';
if (!$diskTotal) {
    $diskTotal = @disk_total_space('C:');
    $diskFree = @disk_free_space('C:');
    $diskDrive = 'Disk (C:)';
}
$diskUsed = $diskTotal - $diskFree;
$diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100) : 0;
$systemInfo['disk'] = [
    'drive' => $diskDrive,
    'used' => $diskUsed,
    'total' => $diskTotal,
    'percent' => $diskPercent,
    'display' => formatBytesGiB($diskUsed) . ' / ' . formatBytesGiB($diskTotal) . ' (' . $diskPercent . '%)',
    'color' => getColor($diskPercent)
];

// Get CPU Usage
$cpuUsage = 0;
if (isShellEnabled()) {
    if ($isWindows) {
        $wmicCpuLoad = @shell_exec('wmic cpu get LoadPercentage /value 2>&1');
        if (preg_match('/LoadPercentage=(\d+)/i', $wmicCpuLoad, $loadMatch)) {
            $cpuUsage = intval($loadMatch[1]);
        }
    }
}
$systemInfo['cpuUsage'] = [
    'percent' => $cpuUsage,
    'color' => getColor($cpuUsage)
];

// Return JSON response
echo json_encode([
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'system' => $systemInfo
]);
