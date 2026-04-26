<?php

namespace App\Services;

class CancellationReasonService
{
    public static function classify(string $reasonKey): string
    {
        $valid = ['illness', 'personal_issue', 'force_majeure', 'technical_issue'];
        $doubtful = ['no_time', 'uncomfortable', 'cant_now'];
        $invalid = ['no_reason'];

        if (in_array($reasonKey, $valid)) return 'valid';
        if (in_array($reasonKey, $doubtful)) return 'doubtful';
        if (in_array($reasonKey, $invalid)) return 'invalid';

        return 'unknown';
    }
}
