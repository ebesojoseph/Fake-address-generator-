<?php
// includes/AddressGenerator.php

namespace App;

use Faker\Factory;

class AddressGenerator
{
    /**
     * Generate a complete address using Faker for the given locale.
     * Falls back to en_US if Faker has no native support.
     */
    public static function generate(string $locale = 'en_US', string $gender = 'random'): array
    {
        $fakerLocale = LocaleRegistry::fakerLocale($locale);
        $localeInfo  = LocaleRegistry::get($locale);

        try {
            $faker = Factory::create($fakerLocale);
        } catch (\Throwable $e) {
            $faker = Factory::create('en_US');
        }

        // Gender
        if ($gender === 'random') {
            $gender = rand(0, 1) ? 'male' : 'female';
        }

        // Name
        $firstName = $gender === 'female' ? $faker->firstNameFemale() : $faker->firstNameMale();
        $lastName  = $faker->lastName();

        // Build address fields — call each method safely
        $fields = [
            'name'       => $firstName . ' ' . $lastName,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'gender'     => ucfirst($gender),
            'locale'     => $locale,
            'faker_locale' => $fakerLocale,
            'country'    => $localeInfo[1] ?? $fakerLocale,
            'country_code' => $localeInfo[3] ?? '',
            'flag'       => $localeInfo[4] ?? '',
            'generated_at' => date('Y-m-d H:i:s'),
        ];

        // Address fields — each wrapped to avoid exceptions on unsupported locales
        $fields['street_address'] = self::try($faker, 'streetAddress',  fn() => $faker->address);
        $fields['city']           = self::try($faker, 'city',           fn() => '');
        $fields['state']          = self::try($faker, 'state',          fn() => '');
        $fields['state_abbr']     = self::try($faker, 'stateAbbr',      fn() => '');
        $fields['postcode']       = self::try($faker, 'postcode',       fn() => '');
        preg_match('/\(([^)]+)\)/', $localeInfo[1] ?? '', $m);
        $fields['country_name']   = $m[1] ?? ($localeInfo[1] ?? '');
        $fields['latitude']       = (string)self::try($faker, 'latitude',    fn() => '');
        $fields['longitude']      = (string)self::try($faker, 'longitude',   fn() => '');
        $fields['phone']          = self::try($faker, 'phoneNumber',    fn() => '');
        $fields['mobile']         = self::try($faker, 'e164PhoneNumber', fn() => '');
        $fields['email']          = self::try($faker, 'safeEmail',      fn() => '');
        $fields['username']       = self::try($faker, 'userName',       fn() => '');
        $fields['company']        = self::try($faker, 'company',        fn() => '');
        $fields['job_title']      = self::try($faker, 'jobTitle',       fn() => '');
        $fields['time_zone']      = self::try($faker, 'timezone',       fn() => '');
        $fields['ssn']            = self::try($faker, 'ssn',            fn() => '');

        // Remove empty fields so the template can conditionally render them
        return array_filter($fields, fn($v) => $v !== '' && $v !== null);
    }

    /** Safely call a faker property/method, fall back to $default callable */
    private static function try(\Faker\Generator $faker, string $prop, callable $default): mixed
    {
        try {
            return $faker->$prop;
        } catch (\Throwable) {
            try {
                return $default();
            } catch (\Throwable) {
                return '';
            }
        }
    }

    /** Log a generation event */
    public static function log(array $address): void
    {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $ip  = get_ip();
            $geo = geolocate_ip($ip);

            get_db()->prepare(
                'INSERT INTO generation_logs
                 (session_id, ip_address, country, region, city, country_code, generated_locale, generated_data)
                 VALUES (?,?,?,?,?,?,?,?)'
            )->execute([
                session_id(),
                $ip,
                $geo['country']     ?? null,
                $geo['regionName']  ?? null,
                $geo['city']        ?? null,
                $geo['countryCode'] ?? null,
                $address['locale']  ?? 'en_US',
                json_encode($address),
            ]);
        } catch (\Throwable $e) {
            error_log('Generation log error: ' . $e->getMessage());
        }
    }
}
