$scriptDir = $PSScriptRoot
if (-not $scriptDir) { $scriptDir = "c:\xampp\htdocs\TMS (final)\task_management_system_office" }

$rate = 44100
$dur = 0.45
$samples = [int]($rate * $dur)
$ms = New-Object System.IO.MemoryStream
$bw = New-Object System.IO.BinaryWriter $ms

# RIFF Header
$bw.Write([System.Text.Encoding]::ASCII.GetBytes('RIFF'))
$bw.Write([int](36 + $samples * 2))
$bw.Write([System.Text.Encoding]::ASCII.GetBytes('WAVEfmt '))
$bw.Write([int]16)
$bw.Write([int16]1) # PCM format
$bw.Write([int16]1) # mono channel
$bw.Write([int]$rate)
$bw.Write([int]($rate * 2)) # byte rate
$bw.Write([int16]2) # block align
$bw.Write([int16]16) # bits per sample
$bw.Write([System.Text.Encoding]::ASCII.GetBytes('data'))
$bw.Write([int]($samples * 2))

for ($i = 0; $i -lt $samples; $i++) {
    $t = $i / $rate
    $s1 = [Math]::Sin(2 * [Math]::PI * 587.33 * $t) * [Math]::Exp(-$t * 8.0) * 0.35
    $s2 = 0
    if ($t -ge 0.08) {
        $t2 = $t - 0.08
        $s2 = [Math]::Sin(2 * [Math]::PI * 880.0 * $t2) * [Math]::Exp(-$t2 * 6.0) * 0.45 + [Math]::Sin(2 * [Math]::PI * 1760.0 * $t2) * [Math]::Exp(-$t2 * 6.0) * 0.1
    }
    $val = [Math]::Max(-1.0, [Math]::Min(1.0, ($s1 + $s2)))
    $bw.Write([int16]($val * 32767))
}

$target1 = Join-Path $scriptDir "public\sounds"
$target2 = Join-Path $scriptDir "sounds"
$null = New-Item -ItemType Directory -Force -Path $target1
$null = New-Item -ItemType Directory -Force -Path $target2

[System.IO.File]::WriteAllBytes((Join-Path $target1 "notification.wav"), $ms.ToArray())
[System.IO.File]::WriteAllBytes((Join-Path $target2 "notification.wav"), $ms.ToArray())
Write-Output "SUCCESS: Notification WAV files generated at $target1 ($($ms.Length) bytes)"
