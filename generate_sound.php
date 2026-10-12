<?php
// Generates a clean 44.1kHz 16-bit mono WAV bell chime
$sampleRate = 44100;
$duration = 0.45;
$numSamples = (int)($sampleRate * $duration);

$data = '';
for ($i = 0; $i < $numSamples; $i++) {
    $t = $i / $sampleRate;
    // Tone 1: 587.33 Hz (D5) with exponential decay
    $env1 = exp(-$t * 8.0);
    $tone1 = sin(2 * M_PI * 587.33 * $t) * $env1 * 0.4;

    // Tone 2: 880.00 Hz (A5) starting at 0.08s
    $tone2 = 0;
    if ($t >= 0.08) {
        $t2 = $t - 0.08;
        $env2 = exp(-$t2 * 6.5);
        $tone2 = sin(2 * M_PI * 880.00 * $t2) * $env2 * 0.5;
        // Overtone
        $tone2 += sin(2 * M_PI * 1760.00 * $t2) * $env2 * 0.1;
    }

    $sample = $tone1 + $tone2;
    $sample = max(-1.0, min(1.0, $sample));
    $intSample = (int)($sample * 32767);
    $data .= pack('v', $intSample);
}

$dataSize = strlen($data);
$header = 'RIFF' . pack('V', 36 + $dataSize) . 'WAVE';
$header .= 'fmt ' . pack('V', 16) . pack('v', 1) . pack('v', 1); // PCM mono
$header .= pack('V', $sampleRate) . pack('V', $sampleRate * 2) . pack('v', 2) . pack('v', 16);
$header .= 'data' . pack('V', $dataSize);

$wavContent = $header . $data;

@mkdir(__DIR__ . '/public/sounds', 0755, true);
@mkdir(__DIR__ . '/sounds', 0755, true);
file_put_contents(__DIR__ . '/public/sounds/notification.wav', $wavContent);
file_put_contents(__DIR__ . '/sounds/notification.wav', $wavContent);

echo "WAV generated successfully: " . strlen($wavContent) . " bytes\n";
