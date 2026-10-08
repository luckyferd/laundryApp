<?php

if (! function_exists('rupiah')) {
    /**
     * Format a number as Indonesian Rupiah, e.g. 15000 -> "Rp 15.000".
     */
    function rupiah($amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }
}
