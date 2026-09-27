<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Generate an inline SVG string for the given content.
     */
    public function generateSvg(string $content, int $size = 250): string
    {
        return QrCode::size($size)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($content);
    }
}
