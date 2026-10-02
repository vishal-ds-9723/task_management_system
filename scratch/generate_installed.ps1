$installedJsonPath = 'c:\xampp\htdocs\TMS (final)\task_management_system_office\vendor\composer\installed.json'
$installedPhpPath  = 'c:\xampp\htdocs\TMS (final)\task_management_system_office\vendor\composer\installed.php'

$data = Get-Content -LiteralPath $installedJsonPath -Raw -Encoding UTF8 | ConvertFrom-Json

$devNames = [System.Collections.Generic.HashSet[string]]::new([System.StringComparer]::OrdinalIgnoreCase)
if ($data.'dev-package-names') {
    foreach ($name in $data.'dev-package-names') {
        $devNames.Add($name) | Out-Null
    }
}

$sb = [System.Text.StringBuilder]::new()
$sb.AppendLine("<?php return array(") | Out-Null
$sb.AppendLine("    'root' => array(") | Out-Null
$sb.AppendLine("        'name' => 'laravel/laravel',") | Out-Null
$sb.AppendLine("        'pretty_version' => 'dev-main',") | Out-Null
$sb.AppendLine("        'version' => 'dev-main',") | Out-Null
$sb.AppendLine("        'reference' => '3887f1bf1799c992121d6b3c541c979bd0188e3d',") | Out-Null
$sb.AppendLine("        'type' => 'project',") | Out-Null
$sb.AppendLine("        'install_path' => __DIR__ . '/../../',") | Out-Null
$sb.AppendLine("        'aliases' => array(),") | Out-Null
$sb.AppendLine("        'dev' => true,") | Out-Null
$sb.AppendLine("    ),") | Out-Null
$sb.AppendLine("    'versions' => array(") | Out-Null
$sb.AppendLine("        'laravel/laravel' => array(") | Out-Null
$sb.AppendLine("            'pretty_version' => 'dev-main',") | Out-Null
$sb.AppendLine("            'version' => 'dev-main',") | Out-Null
$sb.AppendLine("            'reference' => '3887f1bf1799c992121d6b3c541c979bd0188e3d',") | Out-Null
$sb.AppendLine("            'type' => 'project',") | Out-Null
$sb.AppendLine("            'install_path' => __DIR__ . '/../../',") | Out-Null
$sb.AppendLine("            'aliases' => array(),") | Out-Null
$sb.AppendLine("            'dev_requirement' => false,") | Out-Null
$sb.AppendLine("        ),") | Out-Null

foreach ($pkg in $data.packages) {
    $name = $pkg.name
    $prettyVersion = $pkg.version
    $versionNorm = $pkg.version_normalized
    $ref = if ($pkg.dist.reference) { $pkg.dist.reference } elseif ($pkg.source.reference) { $pkg.source.reference } else { "" }
    $type = if ($pkg.type) { $pkg.type } else { "library" }
    $installPath = if ($pkg.'install-path') { $pkg.'install-path' } else { "../" + $name }
    $isDev = $devNames.Contains($name)
    $isDevStr = if ($isDev) { "true" } else { "false" }

    $sb.AppendLine("        '$name' => array(") | Out-Null
    $sb.AppendLine("            'pretty_version' => '$prettyVersion',") | Out-Null
    $sb.AppendLine("            'version' => '$versionNorm',") | Out-Null
    $sb.AppendLine("            'reference' => '$ref',") | Out-Null
    $sb.AppendLine("            'type' => '$type',") | Out-Null
    $sb.AppendLine("            'install_path' => __DIR__ . '/$installPath',") | Out-Null
    $sb.AppendLine("            'aliases' => array(),") | Out-Null
    $sb.AppendLine("            'dev_requirement' => $isDevStr,") | Out-Null
    $sb.AppendLine("        ),") | Out-Null
}

$sb.AppendLine("    ),") | Out-Null
$sb.AppendLine(");") | Out-Null

[System.IO.File]::WriteAllText($installedPhpPath, $sb.ToString(), [System.Text.Encoding]::UTF8)
Write-Host "Generated installed.php successfully!"
