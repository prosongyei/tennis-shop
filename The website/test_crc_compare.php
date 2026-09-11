<?php
require 'test_crc.php';

function calculateCrc16_old(string $data): string
{
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($data); $i++) {
        $crc ^= (ord($data[$i]) << 8);
        for ($j = 0; $j < 8; $j++) {
            if ($crc & 0x8000) {
                $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
            } else {
                $crc = ($crc << 1) & 0xFFFF;
            }
        }
    }
    return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

$testStr = "00020101021229190015toslengsey@abaa520459995303840540546.505802KH5920TOSLENGSEY BADMINTON6010PHNOM PENH62170113ORD-2026-00016304";
echo "Old bitwise: " . calculateCrc16_old($testStr) . "\n";
echo "Table CRC:   " . crc16_table($testStr) . "\n";
