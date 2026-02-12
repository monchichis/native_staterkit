<!--
*
*  INSPINIA - Responsive Admin Theme
*  version 2.9.2
*
-->
<?php 
    include('connection/connection.php');
    // Check Session (starts session if needed)
    checkSession();
    
    // Check apakah terdapat pesan notifikasi
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        $notificationType = $_SESSION['notification_type'];

        // Hapus variabel sesi setelah mengambil nilainya
        unset($_SESSION['notification']);
        unset($_SESSION['notification_type']);

        // Tampilkan notifikasi menggunakan JavaScript (Toastr)
        // Use json_encode to properly escape quotes and special characters
        $safeNotification = json_encode($notification);
        echo "<script>
                setTimeout(function() {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 4000
                    };
                    toastr.$notificationType($safeNotification); 
                }, 1300);
            </script>";
    }

    function isShellEnabled() {
        return function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))));
    }

    // Hanya berfungsi di lingkungan Windows
    function getConnectedSSID() {
        if (!isShellEnabled()) return 'N/A';
        
        $output = @shell_exec('netsh wlan show interfaces');
        if ($output) {
            preg_match('/SSID\s*:\s*(.*)/i', $output, $matches);
            return isset($matches[1]) ? trim($matches[1]) : 'Not connected to Wi-Fi';
        }
        return 'Unknown';
    }

    $ssid = getConnectedSSID();

    $systemInfo = '';
    if (isShellEnabled()) {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $systemInfo = @shell_exec('uname -a');
        } else {
            // Limited to 300 chars to avoid massive output
            $systemInfo = substr(@shell_exec('systeminfo'), 0, 1000) . '...';
        }
    } else {
        $systemInfo = 'System info unavailable (shell_exec disabled)';
    }

?>
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>INSPINIA | Dashboard</title>

    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">

    <!-- Toastr style -->
    <link href="assets/css/plugins/toastr/toastr.min.css" rel="stylesheet">

    <!-- Gritter -->
    <link href="assets/js/plugins/gritter/jquery.gritter.css" rel="stylesheet">

    <link href="assets/css/animate.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/v/bs4/dt-1.13.8/datatables.min.css" rel="stylesheet">
    <!-- Sweetalert -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- c3 gauge chart -->
    <link rel="stylesheet" href="assets/css/plugins/c3/c3.min.css">
   
</head>
<?php include 'connection/connection.php';?>
<?php include 'helper/utility.php';?>
<body>
    <div id="wrapper">
        <!-- sidebar --> 
            <?php include 'template/sidebar.php'; ?>
        <!-- end sidebar --> 

        
        <div id="page-wrapper" class="gray-bg dashboard-1">
            <!-- header -->
             <?php include 'template/header.php'; ?>
            <!-- end header --> 
            
            <!-- body content -->
            <div class="row  border-bottom white-bg dashboard-header">
                 
                <div class="col-lg-10">
                    <h4><?= $_SESSION['level'];?> Dashboard</h4>
                    
                </div>
                <!-- batasi session level akses anda disini -->
                <?php if($_SESSION['level'] == 'SuperAdmin'):?>
                <div class="col-lg-1">
                    
                    <button class="btn btn-danger btn-sm" onclick="confirmUninstall('<?= $database_name ?>')" title="Uninstall Application"><i class="fa fa-power-off"></i></button>
                </div>
                <div class="col-lg-1">
                   <h4><p class="small font-bold">
                     <?php if (checkInternetConnection()) :?>
                        <span><i class="fa fa-circle text-navy"></i> Online</span>
                        <?php else :?>
                        <span><i class="fa fa-circle text-danger"></i> Offline</span>
                        <?php endif;?>
                    </p></h4>                   
                </div> 
                <hr>
                        <div>
                        <div>
                </div>
            </div>
            

            
           
                
                <!-- batasi session level akses anda disini -->
            </div>
            <div class="wrapper wrapper-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-md-6">                   
                                <!--OST Widget code start-->
                                    <div style="text-align:right;"><div style="min-height:360px;"><div style="width:100%;height:0;padding-bottom:50%;position:relative;">
                                        <iframe style="border:none;position:absolute;top:0;left:0;width:100%;height:100%;min-height:360px;border:none;overflow:hidden !important;" src="//openspeedtest.com/speedtest">

                                        </iframe>
                                    </div>
                                    </div>Provided by <a href="https://openspeedtest.com">OpenSpeedtest.com</a></div>
                                <!-- OST Widget code end -->
                            </div>
                            <div class="col-md-6">
                                <div class="widget style1 lazur-bg">
                                    <div class="row">
                                        <div class="col-4">
                                            <i class="fa fa-rss fa-5x"></i>
                                        </div>
                                        <div class="col-8 text-right">
                                            <span> Connected To </span>
                                            <h2 class="font-bold"><?= json_encode($ssid)?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="widget style1 yellow-bg">
                                    <div class="row">
                                        <div class="col-4">
                                            <i class="fa fa-globe fa-5x"></i>
                                        </div>
                                        <div class="col-8 text-right">
                                            <span> Browser Name </span>
                                            <h2 class="font-bold"><?= $browser_name ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="ibox">
                                    <div class="ibox-title" style="background: #2d2d2d; border-color: #444;">
                                        <h4 style="color: #00ff88;"><i class="fa fa-server"></i> System Resources</h4>
                                        <div class="ibox-tools">
                                            <a class="collapse-link" href="">
                                                <i class="fa fa-chevron-up" style="color: #888;"></i>
                                            </a>
                                            <a id="refresh-resources" href="javascript:void(0)" title="Refresh">
                                                <i class="fa fa-refresh" style="color: #888;"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="ibox-content" style="background: #1a1a2e; color: #fff;">
                                        <?php
                                        // Get RAM info
                                        $ramTotal = 0;
                                        $ramUsed = 0;
                                        $ramPercent = 0;
                                        
                                        // Get Storage info
                                        $diskTotal = @disk_total_space('/');
                                        $diskFree = @disk_free_space('/');
                                        if (!$diskTotal) {
                                            $diskTotal = @disk_total_space('C:');
                                            $diskFree = @disk_free_space('C:');
                                        }
                                        $diskUsed = $diskTotal - $diskFree;
                                        $diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 1) : 0;
                                        
                                        // formatBytes() function already exists in helper/utility.php
                                        
                                        // Try to get RAM info
                                        if (isShellEnabled()) {
                                            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                                // Windows
                                                $totalMem = @shell_exec('wmic computersystem get TotalPhysicalMemory /value 2>&1');
                                                $freeMem = @shell_exec('wmic os get FreePhysicalMemory /value 2>&1');
                                                
                                                if (preg_match('/TotalPhysicalMemory=(\d+)/i', $totalMem, $totalMatch)) {
                                                    $ramTotal = (int)$totalMatch[1];
                                                }
                                                if (preg_match('/FreePhysicalMemory=(\d+)/i', $freeMem, $freeMatch)) {
                                                    $ramFree = (int)$freeMatch[1] * 1024; // KB to Bytes
                                                    $ramUsed = $ramTotal - $ramFree;
                                                }
                                            } else {
                                                // Linux
                                                $memInfo = @shell_exec('cat /proc/meminfo 2>/dev/null');
                                                if (preg_match('/MemTotal:\s+(\d+)/i', $memInfo, $totalMatch)) {
                                                    $ramTotal = (int)$totalMatch[1] * 1024;
                                                }
                                                if (preg_match('/MemAvailable:\s+(\d+)/i', $memInfo, $availMatch)) {
                                                    $ramUsed = $ramTotal - ((int)$availMatch[1] * 1024);
                                                }
                                            }
                                        }
                                        
                                        $ramPercent = $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 1) : 0;
                                        
                                        // Color based on usage
                                        $ramColor = $ramPercent < 50 ? '#4caf50' : ($ramPercent < 80 ? '#ffeb3b' : '#f44336');
                                        $diskColor = $diskPercent < 50 ? '#4caf50' : ($diskPercent < 80 ? '#ffeb3b' : '#f44336');
                                        ?>
                                        
                                        <style>
                                            .resource-item {
                                                margin-bottom: 20px;
                                            }
                                            .resource-header {
                                                display: flex;
                                                justify-content: space-between;
                                                margin-bottom: 8px;
                                                font-family: 'Consolas', monospace;
                                            }
                                            .resource-label {
                                                color: #00d4ff;
                                                font-weight: bold;
                                            }
                                            .resource-value {
                                                color: #aaa;
                                                font-size: 12px;
                                            }
                                            .htop-progress {
                                                height: 20px;
                                                background: #333;
                                                border-radius: 3px;
                                                overflow: hidden;
                                                position: relative;
                                            }
                                            .htop-progress-bar {
                                                height: 100%;
                                                transition: width 0.5s ease-in-out;
                                                display: flex;
                                                align-items: center;
                                                justify-content: flex-end;
                                                padding-right: 8px;
                                                font-size: 11px;
                                                font-weight: bold;
                                                color: #000;
                                            }
                                            .htop-progress-text {
                                                position: absolute;
                                                right: 10px;
                                                top: 50%;
                                                transform: translateY(-50%);
                                                font-size: 11px;
                                                color: #fff;
                                            }
                                            .resource-grid {
                                                display: grid;
                                                grid-template-columns: 60px 1fr;
                                                gap: 10px;
                                                align-items: center;
                                            }
                                            .resource-icon {
                                                font-size: 28px;
                                                text-align: center;
                                            }
                                        </style>
                                        
                                        <!-- RAM Usage -->
                                        <div class="resource-item">
                                            <div class="resource-grid">
                                                <div class="resource-icon" style="color: <?= $ramColor ?>;">
                                                    <i class="fa fa-microchip"></i>
                                                </div>
                                                <div>
                                                    <div class="resource-header">
                                                        <span class="resource-label">RAM</span>
                                                        <span class="resource-value" id="ram-text">
                                                            <?= formatBytes($ramUsed) ?> / <?= formatBytes($ramTotal) ?>
                                                        </span>
                                                    </div>
                                                    <div class="htop-progress">
                                                        <div class="htop-progress-bar" id="ram-bar" style="width: <?= $ramPercent ?>%; background: <?= $ramColor ?>;">
                                                        </div>
                                                        <span class="htop-progress-text" id="ram-percent"><?= $ramPercent ?>%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Storage Usage -->
                                        <div class="resource-item">
                                            <div class="resource-grid">
                                                <div class="resource-icon" style="color: <?= $diskColor ?>;">
                                                    <i class="fa fa-hdd-o"></i>
                                                </div>
                                                <div>
                                                    <div class="resource-header">
                                                        <span class="resource-label">STORAGE</span>
                                                        <span class="resource-value" id="disk-text">
                                                            <?= formatBytes($diskUsed) ?> / <?= formatBytes($diskTotal) ?>
                                                        </span>
                                                    </div>
                                                    <div class="htop-progress">
                                                        <div class="htop-progress-bar" id="disk-bar" style="width: <?= $diskPercent ?>%; background: <?= $diskColor ?>;">
                                                        </div>
                                                        <span class="htop-progress-text" id="disk-percent"><?= $diskPercent ?>%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- CPU Usage -->
                                        <?php 
                                        $cpuUsage = 0;
                                        
                                        if (isShellEnabled()) {
                                            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                                // Windows - Get CPU Usage via wmic
                                                $wmicCpu = @shell_exec('wmic cpu get LoadPercentage /value 2>&1');
                                                if ($wmicCpu && preg_match('/LoadPercentage=(\d+)/i', $wmicCpu, $cpuMatch)) {
                                                    $cpuUsage = intval($cpuMatch[1]);
                                                }
                                            } else {
                                                // Linux - Get CPU Usage from /proc/stat
                                                $stat1 = @file_get_contents('/proc/stat');
                                                usleep(100000); // 100ms delay
                                                $stat2 = @file_get_contents('/proc/stat');
                                                
                                                if ($stat1 && $stat2) {
                                                    preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/m', $stat1, $m1);
                                                    preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/m', $stat2, $m2);
                                                    
                                                    if ($m1 && $m2) {
                                                        $idle1 = $m1[4];
                                                        $idle2 = $m2[4];
                                                        $total1 = array_sum(array_slice($m1, 1));
                                                        $total2 = array_sum(array_slice($m2, 1));
                                                        
                                                        $idleDiff = $idle2 - $idle1;
                                                        $totalDiff = $total2 - $total1;
                                                        
                                                        if ($totalDiff > 0) {
                                                            $cpuUsage = round(100 * (1 - ($idleDiff / $totalDiff)), 1);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        
                                        // CPU color: Green < 50%, Yellow 50-80%, Red > 80%
                                        $cpuColor = $cpuUsage < 50 ? '#4caf50' : ($cpuUsage < 80 ? '#ffeb3b' : '#f44336');
                                        ?>
                                        <div class="resource-item">
                                            <div class="resource-grid">
                                                <div class="resource-icon" style="color: <?= $cpuColor ?>;">
                                                    <i class="fa fa-tachometer"></i>
                                                </div>
                                                <div>
                                                    <div class="resource-header">
                                                        <span class="resource-label">CPU USAGE</span>
                                                        <span class="resource-value" id="cpu-text">
                                                            <?= $cpuUsage ?>%
                                                        </span>
                                                    </div>
                                                    <div class="htop-progress">
                                                        <div class="htop-progress-bar" id="cpu-bar" style="width: <?= $cpuUsage ?>%; background: <?= $cpuColor ?>;"></div>
                                                        <span class="htop-progress-text" id="cpu-percent"><?= $cpuUsage ?>%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="col-md-12">
                                
                                <div class="ibox">
                                    <div class="ibox-title" style="background: #1e1e1e; border-color: #333;">
                                        <h3 style="color: #0f0;"><i class="fa fa-terminal"></i> System Info</h3>
                                        <div class="ibox-tools">
                                            <a class="collapse-link" href="">
                                                <i class="fa fa-chevron-up" style="color: #888;"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="ibox-content" style="background: #1e1e1e; color: #fff; padding: 0;">
                                        <?php
                                        // Fetch system info from API (for maintainability)
                                        $systemInfo = [];
                                        $apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/core/api/system_resources.php';
                                        
                                        // Use internal include for better performance
                                        ob_start();
                                        $originalSession = $_SESSION;
                                        include __DIR__ . '/core/api/system_resources.php';
                                        $apiResponse = ob_get_clean();
                                        $_SESSION = $originalSession;
                                        
                                        $apiData = json_decode($apiResponse, true);
                                        if ($apiData && isset($apiData['system'])) {
                                            $systemInfo = $apiData['system'];
                                        }
                                        
                                        // Fallback values
                                        $header = $systemInfo['header'] ?? (($_SESSION['username'] ?? 'user') . '@' . gethostname());
                                        $os = $systemInfo['os'] ?? PHP_OS;
                                        $host = $systemInfo['host'] ?? 'N/A';
                                        $kernel = $systemInfo['kernel'] ?? php_uname('r');
                                        $motherboard = $systemInfo['motherboard'] ?? 'N/A';
                                        $uptime = $systemInfo['uptime'] ?? 'N/A';
                                        $shell = $systemInfo['shell'] ?? 'N/A';
                                        $resolution = $systemInfo['resolution'] ?? 'N/A';
                                        $terminal = $systemInfo['terminal'] ?? 'N/A';
                                        $cpu = $systemInfo['cpu'] ?? 'N/A';
                                        $gpus = $systemInfo['gpu'] ?? [];
                                        $memory = $systemInfo['memory']['display'] ?? 'N/A';
                                        $disk = $systemInfo['disk'] ?? [];
                                        $diskDisplay = $disk['display'] ?? 'N/A';
                                        $diskDrive = $disk['drive'] ?? 'Disk';
                                        ?>
                                        <style>
                                            .neofetch-container {
                                                display: flex;
                                                flex-wrap: wrap;
                                                padding: 20px;
                                                font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
                                                font-size: 13px;
                                                line-height: 1.6;
                                                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
                                                border-radius: 0 0 4px 4px;
                                                min-height: 280px;
                                                align-items: flex-start;
                                            }
                                            .neofetch-logo {
                                                flex: 0 0 200px;
                                                padding-right: 25px;
                                                color: #4fc3f7;
                                                white-space: pre;
                                                font-size: 11px;
                                                line-height: 1.2;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                            }
                                            .neofetch-info {
                                                flex: 1;
                                                min-width: 350px;
                                            }
                                            .neofetch-header {
                                                color: #4fc3f7;
                                                font-weight: bold;
                                                font-size: 14px;
                                                margin-bottom: 5px;
                                            }
                                            .neofetch-separator {
                                                color: #666;
                                                margin-bottom: 10px;
                                            }
                                            .neofetch-row {
                                                display: flex;
                                                margin-bottom: 2px;
                                            }
                                            .neofetch-label {
                                                color: #4fc3f7;
                                                font-weight: bold;
                                                min-width: 130px;
                                            }
                                            .neofetch-label::after {
                                                content: ':';
                                            }
                                            .neofetch-value {
                                                color: #e0e0e0;
                                                word-break: break-word;
                                            }
                                            .neofetch-colors {
                                                display: flex;
                                                gap: 0;
                                                margin-top: 15px;
                                            }
                                            .neofetch-color-block {
                                                width: 30px;
                                                height: 20px;
                                            }
                                            @media (max-width: 768px) {
                                                .neofetch-container {
                                                    flex-direction: column;
                                                    padding: 15px;
                                                }
                                                .neofetch-logo {
                                                    flex: none;
                                                    padding-right: 0;
                                                    padding-bottom: 15px;
                                                    font-size: 9px;
                                                }
                                                .neofetch-info {
                                                    min-width: auto;
                                                }
                                                .neofetch-label {
                                                    min-width: 100px;
                                                }
                                            }
                                        </style>
                                        <div class="neofetch-container">
                                            <div class="neofetch-logo">
                                                <?php
                                                $osType = strtoupper(substr(PHP_OS, 0, 3));
                                                if ($osType === 'WIN') {
                                                    // Windows Logo (Block Style matching screenshot)
                                                    echo <<<EOT
<pre style="color: #4fc3f7; margin: 0; line-height: 1.0; font-family: monospace; font-size: 10px;">
lllllllllllll  lllllllllllll
lllllllllllll  lllllllllllll
lllllllllllll  lllllllllllll
lllllllllllll  lllllllllllll
lllllllllllll  lllllllllllll
lllllllllllll  lllllllllllll

lllllllllllll  lllllllllllll
lllllllllllll  lllllllllllll
lllllllllllll  lllllllllllll
lllllllllllll  lllllllllllll
lllllllllllll  lllllllllllll
lllllllllllll  lllllllllllll
</pre>
EOT;
                                                } else {
                                                    // Linux/Tux Logo
                                                     echo <<<EOT
<pre style="margin: 0; line-height: 1.1; font-family: monospace;">
   <span style="color: #fce94f">.--.</span>
  <span style="color: #fce94f">|o_o |</span>
  <span style="color: #fce94f">|:_/ |</span>
 <span style="color: #fce94f">//   \ \</span>
<span style="color: #fce94f">(|     |)</span>
<span style="color: #fce94f">/'\\_   _/`\</span>
<span style="color: #fce94f">\\___)=(___/</span>
</pre>
EOT;
                                                }
                                                ?>
                                            </div>
                                            <div class="neofetch-info">
                                                <div class="neofetch-header">
                                                    <?= htmlspecialchars($header) ?>
                                                </div>
                                                <div class="neofetch-separator">
                                                    ───────────────────────────
                                                </div>
                                                <div class="neofetch-row">
                                                    <span class="neofetch-label">OS</span>
                                                    <span class="neofetch-value"><?= htmlspecialchars($os) ?></span>
                                                </div>
                                                <div class="neofetch-row">
                                                    <span class="neofetch-label">Host</span>
                                                    <span class="neofetch-value"><?= htmlspecialchars($host) ?></span>
                                                </div>
                                                <div class="neofetch-row">
                                                    <span class="neofetch-label">Kernel</span>
                                                    <span class="neofetch-value"><?= htmlspecialchars($kernel) ?></span>
                                                </div>
                                                <div class="neofetch-row">
                                                    <span class="neofetch-label">Motherboard</span>
                                                    <span class="neofetch-value"><?= htmlspecialchars($motherboard) ?></span>
                                                </div>
                                                <div class="neofetch-row">
                                                    <span class="neofetch-label">Uptime</span>
                                                    <span class="neofetch-value"><?= htmlspecialchars($uptime) ?></span>
                                                </div>
                                                <div class="neofetch-row">
                                                    <span class="neofetch-label">Shell</span>
                                                    <span class="neofetch-value"><?= htmlspecialchars($shell) ?></span>
                                                </div>
                                                <div class="neofetch-row">
                                                    <span class="neofetch-label">Resolution</span>
                                                    <span class="neofetch-value"><?= htmlspecialchars($resolution) ?></span>
                                                </div>
                                                <div class="neofetch-row">
                                                    <span class="neofetch-label">Terminal</span>
                                                    <span class="neofetch-value"><?= htmlspecialchars($terminal) ?></span>
                                                </div>
                                                <div class="neofetch-row">
                                                    <span class="neofetch-label">CPU</span>
                                                    <span class="neofetch-value"><?= htmlspecialchars($cpu) ?></span>
                                                </div>
                                                <?php foreach ($gpus as $gpu): ?>
                                                <div class="neofetch-row">
                                                    <span class="neofetch-label">GPU</span>
                                                    <span class="neofetch-value"><?= htmlspecialchars($gpu) ?></span>
                                                </div>
                                                <?php endforeach; ?>
                                                <div class="neofetch-row">
                                                    <span class="neofetch-label">Memory</span>
                                                    <span class="neofetch-value"><?= htmlspecialchars($memory) ?></span>
                                                </div>
                                                <div class="neofetch-row">
                                                    <span class="neofetch-label"><?= htmlspecialchars($diskDrive) ?></span>
                                                    <span class="neofetch-value"><?= htmlspecialchars($diskDisplay) ?></span>
                                                </div>
                                                <div class="neofetch-colors">
                                                    <div class="neofetch-color-block" style="background: #1e1e1e;"></div>
                                                    <div class="neofetch-color-block" style="background: #f44336;"></div>
                                                    <div class="neofetch-color-block" style="background: #4caf50;"></div>
                                                    <div class="neofetch-color-block" style="background: #ffeb3b;"></div>
                                                    <div class="neofetch-color-block" style="background: #2196f3;"></div>
                                                    <div class="neofetch-color-block" style="background: #9c27b0;"></div>
                                                    <div class="neofetch-color-block" style="background: #00bcd4;"></div>
                                                    <div class="neofetch-color-block" style="background: #ffffff;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                
                        </div>
                    </div>
                </div>
            </div>
        <?php endif;?>
            <!-- end body content -->
            <!-- footer -->
                <?php include 'template/footer.php'; ?>
            <!-- end footer -->
        </div> 
        
        
        
        
    </div>

    <!-- Mainly scripts -->
    <script src="assets/js/jquery-3.1.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Flot -->
    <script src="assets/js/plugins/flot/jquery.flot.js"></script>
    <script src="assets/js/plugins/flot/jquery.flot.tooltip.min.js"></script>
    <script src="assets/js/plugins/flot/jquery.flot.spline.js"></script>
    <script src="assets/js/plugins/flot/jquery.flot.resize.js"></script>
    <script src="assets/js/plugins/flot/jquery.flot.pie.js"></script>

    <!-- Peity -->
    <script src="assets/js/plugins/peity/jquery.peity.min.js"></script>
    <script src="assets/js/demo/peity-demo.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="assets/js/inspinia.js"></script>
    <script src="assets/js/plugins/pace/pace.min.js"></script>

    <!-- jQuery UI -->
    <script src="assets/js/plugins/jquery-ui/jquery-ui.min.js"></script>

    <!-- GITTER -->
    <script src="assets/js/plugins/gritter/jquery.gritter.min.js"></script>

    <!-- Sparkline -->
    <script src="assets/js/plugins/sparkline/jquery.sparkline.min.js"></script>

    <!-- Sparkline demo data  -->
    <script src="assets/js/demo/sparkline-demo.js"></script>

    <!-- ChartJS-->
    <script src="assets/js/plugins/chartJs/Chart.min.js"></script>

    <!-- Toastr -->
    <script src="assets/js/plugins/toastr/toastr.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/v/bs4/dt-1.13.8/datatables.min.js"></script>
    <!-- sweetalert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function confirmUninstall(database) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action will uninstall the application!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, uninstall it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If user clicks "Yes," proceed with AJAX request to uninstall
                    $.ajax({
                        url: 'core/uninstall/uninstall.php', // Sesuaikan dengan path yang benar
                        type: 'POST',
                        data: { database: database },
                        success: function(response) {
                            // Handle success
                            // Swal.fire('Uninstalled!', 'Application has been uninstalled.', 'success');
                            // Optionally, you can redirect to another page here
                            window.location.href = 'core/logout.php';
                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            Swal.fire('Error!', 'Failed to uninstall application.', 'error');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        }
    </script>
    <script>
        // Real-time System Resources Update
        document.addEventListener("DOMContentLoaded", function () {
            var refreshBtn = document.getElementById('refresh-resources');
            var isUpdating = false;
            var updateInterval = 5000; // 5 seconds
            
            // Function to update resources via AJAX
            function updateResources() {
                if (isUpdating) return;
                isUpdating = true;
                
                // Add spinning animation to refresh button
                if (refreshBtn) {
                    refreshBtn.querySelector('i').classList.add('fa-spin');
                }
                
                $.ajax({
                    url: 'core/api/system_resources.php',
                    type: 'GET',
                    dataType: 'json',
                    timeout: 10000,
                    success: function(data) {
                        if (data.success) {
                            // Update RAM
                            $('#ram-bar').css({
                                'width': data.ram.percent + '%',
                                'background': data.ram.color
                            });
                            $('#ram-percent').text(data.ram.percent + '%');
                            $('#ram-text').text(data.ram.usedFormatted + ' / ' + data.ram.totalFormatted);
                            $('.resource-icon').eq(0).css('color', data.ram.color);
                            
                            // Update Disk
                            $('#disk-bar').css({
                                'width': data.disk.percent + '%',
                                'background': data.disk.color
                            });
                            $('#disk-percent').text(data.disk.percent + '%');
                            $('#disk-text').text(data.disk.usedFormatted + ' / ' + data.disk.totalFormatted);
                            $('.resource-icon').eq(1).css('color', data.disk.color);
                            
                            // Update PHP Memory
                            $('#php-bar').css({
                                'width': data.php.percent + '%',
                                'background': data.php.color
                            });
                            $('#php-percent').text(data.php.percent + '%');
                            $('#php-text').text(data.php.usedFormatted + ' / ' + data.php.limit);
                            $('.resource-icon').eq(2).css('color', data.php.color);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Failed to fetch system resources:', error);
                    },
                    complete: function() {
                        isUpdating = false;
                        // Remove spinning animation
                        if (refreshBtn) {
                            refreshBtn.querySelector('i').classList.remove('fa-spin');
                        }
                    }
                });
            }
            
            // Manual refresh button
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    updateResources();
                });
            }
            
            // Auto-update every 5 seconds
            setInterval(updateResources, updateInterval);
            
            // Initial update after 2 seconds
            setTimeout(updateResources, 2000);
        });
    </script>
      

    

</body>
</html>
