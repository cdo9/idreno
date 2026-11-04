<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;
use Propaganistas\LaravelPhone\PhoneNumber;

class PhoneFormatter
{
    /**
     * Formater un numéro de téléphone vers le format E164 pour le stockage en base
     *
     * @param  string|null  $phone
     * @return string|null
     */
    public static function formatForStorage($phone)
    {
        if (empty($phone)) {
            return null;
        }

        try {
            $phoneNumber = new PhoneNumber($phone, 'FR');
            Log::info('Formatting phone number for storage: '.$phone);
            if ($phoneNumber->isValid()) {
                $formatted = $phoneNumber->formatNational();
                $formatted = self::clean($formatted);

                return $formatted;
            } else {
                Log::error('Invalid phone number format: '.$phone);

                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exception formatting phone number: '.$phone.' - '.$e->getMessage());

            return null;
        }
    }

    /**
     * Formater un numéro de téléphone pour l'affichage (format national français)
     *
     * @param  string|null  $phone
     * @return string
     */
    public static function formatForDisplay($phone)
    {
        if (empty($phone)) {
            return '';
        }

        try {
            $phoneNumber = new PhoneNumber($phone, 'FR');
            $formatted = $phoneNumber->formatNational();

            return $formatted;
        } catch (\Exception $e) {
            Log::error('Exception formatting phone for display: '.$e->getMessage());

            return $phone; // Retourne le numéro original si le formatage échoue
        }
    }

    /**
     * Valider qu'un numéro de téléphone est valide
     *
     * @param  string|null  $phone
     * @param  array  $countries  Pays autorisés (par défaut FR, BE, CH)
     * @return bool
     */
    public static function isValid($phone, array $countries = ['FR'])
    {
        if (empty($phone)) {
            return false;
        }

        foreach ($countries as $country) {
            try {
                $phoneNumber = new PhoneNumber($phone, $country);
                if ($phoneNumber->isValid()) {
                    return true;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return false;
    }

    /**
     * Détecter le pays d'un numéro de téléphone
     *
     * @param  string|null  $phone
     * @return string|null Code pays ISO (FR, BE, etc.) ou null si non détecté
     */
    public static function detectCountry($phone)
    {
        if (empty($phone)) {
            return null;
        }

        $countries = ['FR'];

        foreach ($countries as $country) {
            try {
                $phoneNumber = new PhoneNumber($phone, $country);
                if ($phoneNumber->isValid()) {
                    return $phoneNumber->getCountry();
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Nettoyer un numéro de téléphone (supprime tout sauf chiffres et +)
     *
     * @param  string|null  $phone
     * @return string
     */
    public static function clean($phone)
    {
        if (empty($phone)) {
            return '';
        }

        return preg_replace('/[^0-9+]/', '', $phone);
    }
}
