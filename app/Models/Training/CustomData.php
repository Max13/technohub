<?php

namespace App\Models\Training;

use Exception;
use Illuminate\Support\Carbon;

/**
 * Training's Custom data ids
 */
enum CustomData : int
{
    case CERTIFICATION_AUTHORITY = 10;
    case CERTIFICATION_EXPIRY_DATE = 9;
    case CERTIFICATION_METHOD = 11;
    case CPF_ELIGIBLE = 4;
    case SCHOOL = 70;

    /**
     * Return the self of the given custom data
     *
     * @param  array $data
     * @return self
     *
     * @throws \Exception
     */
    static public function enum(array $data) : self
    {
        foreach (self::cases() as $case) {
            if ($data['codeRubrique'] === $case->value) {
                return $case;
            }
        }

        throw new Exception("Unknown custom data id for {$data['codeRubrique']}");
    }

    /**
     * Return the value of the custom data
     *
     * @param  array $data
     * @return float|\Illuminate\Support\Carbon|string
     */
    static public function value(array $data)
    {
        if (isset($data['date'])) {
            return $data['date'];
        }

        if (isset($data['montant'])) {
            return floatval($data['montant']);
        }

        if (isset($data['nombre'])) {
            return floatval($data['nombre']);
        }

        if (isset($data['observation'])) {
            return $data['observation'];
        }

        if (isset($data['texteLibre'])) {
            return $data['texteLibre'];
        }

        return $data['valeur']['nomValeur'];
    }
}
