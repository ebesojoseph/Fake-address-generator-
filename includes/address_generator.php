<?php
// includes/address_generator.php — Core fake address generation engine

class AddressGenerator
{
    // -------------------------------------------------------
    // First names by gender (plain arrays — valid as static property)
    // -------------------------------------------------------
    private static array $names = [
        'male' => [
            'first' => ['James','John','Robert','Michael','William','David','Richard','Joseph','Thomas','Charles','Christopher','Daniel','Matthew','Anthony','Mark','Donald','Steven','Paul','Andrew','Joshua','Kenneth','Kevin','Brian','George','Timothy','Ronald','Edward','Jason','Jeffrey','Ryan','Jacob','Gary','Nicholas','Eric','Jonathan','Stephen','Larry','Justin','Scott','Brandon'],
            'last'  => ['Smith','Johnson','Williams','Brown','Jones','Garcia','Miller','Davis','Rodriguez','Martinez','Hernandez','Lopez','Gonzalez','Wilson','Anderson','Thomas','Taylor','Moore','Jackson','Martin','Lee','Perez','Thompson','White','Harris','Sanchez','Clark','Ramirez','Lewis','Robinson'],
        ],
        'female' => [
            'first' => ['Mary','Patricia','Jennifer','Linda','Barbara','Elizabeth','Susan','Jessica','Sarah','Karen','Lisa','Nancy','Betty','Margaret','Sandra','Ashley','Dorothy','Kimberly','Emily','Donna','Michelle','Carol','Amanda','Melissa','Deborah','Stephanie','Rebecca','Sharon','Laura','Cynthia','Kathleen','Helen','Amy','Angela','Shirley','Anna','Brenda','Pamela','Emma','Nicole'],
            'last'  => ['Smith','Johnson','Williams','Brown','Jones','Garcia','Miller','Davis','Rodriguez','Martinez','Hernandez','Lopez','Gonzalez','Wilson','Anderson','Thomas','Taylor','Moore','Jackson','Martin','Lee','Perez','Thompson','White','Harris','Sanchez','Clark','Ramirez','Lewis','Robinson'],
        ],
    ];

    // -------------------------------------------------------
    // Country data — returned by a static METHOD instead of a
    // static property because PHP does not allow closures as
    // class property default values.
    // -------------------------------------------------------
    private static function data(): array
    {
        return [
            'us' => [
                'country' => 'United States',
                'code'    => 'US',
                'streets' => ['Maple Avenue','Oak Street','Pine Road','Elm Drive','Cedar Lane','Sunset Boulevard','Lake View Drive','River Road','Park Place','Highland Avenue','Broadway','Willow Way','Forest Trail','Meadow Lane','Spring Street'],
                'cities'  => [
                    ['city'=>'New York',     'state'=>'New York',     'zip_prefix'=>'100'],
                    ['city'=>'Los Angeles',  'state'=>'California',   'zip_prefix'=>'900'],
                    ['city'=>'Chicago',      'state'=>'Illinois',     'zip_prefix'=>'606'],
                    ['city'=>'Houston',      'state'=>'Texas',        'zip_prefix'=>'770'],
                    ['city'=>'Phoenix',      'state'=>'Arizona',      'zip_prefix'=>'850'],
                    ['city'=>'Philadelphia', 'state'=>'Pennsylvania', 'zip_prefix'=>'191'],
                    ['city'=>'San Antonio',  'state'=>'Texas',        'zip_prefix'=>'782'],
                    ['city'=>'San Diego',    'state'=>'California',   'zip_prefix'=>'921'],
                    ['city'=>'Dallas',       'state'=>'Texas',        'zip_prefix'=>'752'],
                    ['city'=>'San Jose',     'state'=>'California',   'zip_prefix'=>'951'],
                    ['city'=>'Springfield',  'state'=>'Illinois',     'zip_prefix'=>'627'],
                    ['city'=>'Portland',     'state'=>'Oregon',       'zip_prefix'=>'972'],
                ],
            ],
            'uk' => [
                'country' => 'United Kingdom',
                'code'    => 'GB',
                'streets' => ['High Street','Church Road','Victoria Road','Green Lane','Kings Road','Queen Street','Station Road','Park Avenue','London Road','Mill Street','Castle Street','Bridge Road','The Grove','Manor Way','Abbey Road'],
                'cities'  => [
                    ['city'=>'London',     'state'=>'England',  'zip_prefix'=>'SW'],
                    ['city'=>'Manchester', 'state'=>'England',  'zip_prefix'=>'M'],
                    ['city'=>'Birmingham', 'state'=>'England',  'zip_prefix'=>'B'],
                    ['city'=>'Leeds',      'state'=>'England',  'zip_prefix'=>'LS'],
                    ['city'=>'Glasgow',    'state'=>'Scotland', 'zip_prefix'=>'G'],
                    ['city'=>'Edinburgh',  'state'=>'Scotland', 'zip_prefix'=>'EH'],
                    ['city'=>'Cardiff',    'state'=>'Wales',    'zip_prefix'=>'CF'],
                    ['city'=>'Bristol',    'state'=>'England',  'zip_prefix'=>'BS'],
                    ['city'=>'Sheffield',  'state'=>'England',  'zip_prefix'=>'S'],
                    ['city'=>'Liverpool',  'state'=>'England',  'zip_prefix'=>'L'],
                ],
            ],
            'au' => [
                'country' => 'Australia',
                'code'    => 'AU',
                'streets' => ['George Street','Pitt Street','Elizabeth Street','King Street','Market Street','Hunter Street','Victoria Avenue','Bourke Street','Collins Street','Flinders Street','Swanston Street','William Street','Ann Street','Adelaide Street','Creek Street'],
                'cities'  => [
                    ['city'=>'Sydney',     'state'=>'New South Wales',              'zip_prefix'=>'20'],
                    ['city'=>'Melbourne',  'state'=>'Victoria',                     'zip_prefix'=>'30'],
                    ['city'=>'Brisbane',   'state'=>'Queensland',                   'zip_prefix'=>'40'],
                    ['city'=>'Perth',      'state'=>'Western Australia',            'zip_prefix'=>'60'],
                    ['city'=>'Adelaide',   'state'=>'South Australia',              'zip_prefix'=>'50'],
                    ['city'=>'Gold Coast', 'state'=>'Queensland',                   'zip_prefix'=>'42'],
                    ['city'=>'Canberra',   'state'=>'Australian Capital Territory', 'zip_prefix'=>'26'],
                    ['city'=>'Darwin',     'state'=>'Northern Territory',           'zip_prefix'=>'08'],
                ],
            ],
            'ca' => [
                'country' => 'Canada',
                'code'    => 'CA',
                'streets' => ['Main Street','Yonge Street','King Street','Queen Street','Bloor Street','Bay Street','College Street','Dundas Street','Spadina Avenue','Jasper Avenue','Whyte Avenue','Granville Street','Robson Street','Ste-Catherine Street','Peel Street'],
                'cities'  => [
                    ['city'=>'Toronto',     'state'=>'Ontario',          'zip_prefix'=>'M'],
                    ['city'=>'Vancouver',   'state'=>'British Columbia', 'zip_prefix'=>'V'],
                    ['city'=>'Montreal',    'state'=>'Quebec',           'zip_prefix'=>'H'],
                    ['city'=>'Calgary',     'state'=>'Alberta',          'zip_prefix'=>'T'],
                    ['city'=>'Edmonton',    'state'=>'Alberta',          'zip_prefix'=>'T'],
                    ['city'=>'Ottawa',      'state'=>'Ontario',          'zip_prefix'=>'K'],
                    ['city'=>'Winnipeg',    'state'=>'Manitoba',         'zip_prefix'=>'R'],
                    ['city'=>'Quebec City', 'state'=>'Quebec',           'zip_prefix'=>'G'],
                ],
            ],
            'de' => [
                'country' => 'Germany',
                'code'    => 'DE',
                'streets' => ['Hauptstrasse','Bahnhofstrasse','Kirchstrasse','Schulstrasse','Gartenstrasse','Bergstrasse','Waldstrasse','Ringstrasse','Muhlenstrasse','Lindenstrasse','Parkstrasse','Dorfstrasse','Marktplatz','Brunnenstrasse','Schillerstrasse'],
                'cities'  => [
                    ['city'=>'Berlin',      'state'=>'Berlin',                 'zip_prefix'=>'10'],
                    ['city'=>'Hamburg',     'state'=>'Hamburg',                'zip_prefix'=>'20'],
                    ['city'=>'Munich',      'state'=>'Bavaria',                'zip_prefix'=>'80'],
                    ['city'=>'Cologne',     'state'=>'North Rhine-Westphalia', 'zip_prefix'=>'50'],
                    ['city'=>'Frankfurt',   'state'=>'Hesse',                  'zip_prefix'=>'60'],
                    ['city'=>'Stuttgart',   'state'=>'Baden-Wurttemberg',      'zip_prefix'=>'70'],
                    ['city'=>'Dusseldorf',  'state'=>'North Rhine-Westphalia', 'zip_prefix'=>'40'],
                    ['city'=>'Leipzig',     'state'=>'Saxony',                 'zip_prefix'=>'04'],
                ],
            ],
            'jp' => [
                'country' => 'Japan',
                'code'    => 'JP',
                'streets' => ['Sakura Street','Fuji Road','Marunouchi Avenue','Ginza Boulevard','Akihabara Lane','Shinjuku Street','Shibuya Road','Asakusa Way','Harajuku Drive','Roppongi Avenue','Ikebukuro Street','Ueno Road','Nakameguro Lane','Shimokitazawa Street','Koenji Avenue'],
                'cities'  => [
                    ['city'=>'Tokyo',     'state'=>'Tokyo',    'zip_prefix'=>'100'],
                    ['city'=>'Osaka',     'state'=>'Osaka',    'zip_prefix'=>'530'],
                    ['city'=>'Kyoto',     'state'=>'Kyoto',    'zip_prefix'=>'600'],
                    ['city'=>'Sapporo',   'state'=>'Hokkaido', 'zip_prefix'=>'060'],
                    ['city'=>'Nagoya',    'state'=>'Aichi',    'zip_prefix'=>'460'],
                    ['city'=>'Fukuoka',   'state'=>'Fukuoka',  'zip_prefix'=>'810'],
                    ['city'=>'Kobe',      'state'=>'Hyogo',    'zip_prefix'=>'650'],
                    ['city'=>'Hiroshima', 'state'=>'Hiroshima','zip_prefix'=>'730'],
                ],
            ],
        ];
    }

    // -------------------------------------------------------
    // ZIP / postal code — one branch per country
    // -------------------------------------------------------
    private static function makeZip(string $country, string $prefix): string
    {
        switch ($country) {
            case 'us':
                return $prefix . str_pad((string)rand(0, 9999), 4, '0', STR_PAD_LEFT);
            case 'uk':
                return strtoupper($prefix) . rand(1, 99) . ' ' . rand(1, 9) . chr(rand(65, 90)) . chr(rand(65, 90));
            case 'au':
                return $prefix . str_pad((string)rand(0, 99), 2, '0', STR_PAD_LEFT);
            case 'ca':
                return strtoupper($prefix) . rand(1, 9) . chr(rand(65, 90)) . ' ' . rand(1, 9) . chr(rand(65, 90)) . rand(1, 9);
            case 'de':
                return $prefix . str_pad((string)rand(0, 999), 3, '0', STR_PAD_LEFT);
            case 'jp':
                return $prefix . '-' . str_pad((string)rand(0, 9999), 4, '0', STR_PAD_LEFT);
            default:
                return $prefix . rand(1000, 9999);
        }
    }

    // -------------------------------------------------------
    // Phone number — one branch per country
    // -------------------------------------------------------
    private static function makePhone(string $country): string
    {
        switch ($country) {
            case 'us':
            case 'ca':
                return sprintf('+1 (%03d) %03d-%04d', rand(200, 999), rand(100, 999), rand(1000, 9999));
            case 'uk':
                return sprintf('+44 %04d %06d', rand(1000, 9999), rand(100000, 999999));
            case 'au':
                return sprintf('+61 %d %04d %04d', rand(2, 9), rand(1000, 9999), rand(1000, 9999));
            case 'de':
                return sprintf('+49 %03d %07d', rand(30, 899), rand(1000000, 9999999));
            case 'jp':
                return sprintf('+81 %02d-%04d-%04d', rand(3, 90), rand(1000, 9999), rand(1000, 9999));
            default:
                return sprintf('+%d %09d', rand(1, 99), rand(100000000, 999999999));
        }
    }

    // -------------------------------------------------------
    // Public API
    // -------------------------------------------------------

    /**
     * Generate a complete fake address.
     *
     * @param string      $countryCode  'us' | 'uk' | 'au' | 'ca' | 'de' | 'jp'
     * @param string      $gender       'male' | 'female' | 'random'
     * @param string|null $state        Optional state/region filter
     */
    public static function generate(
        string $countryCode = 'us',
        string $gender = 'random',
        ?string $state = null
    ): array {
        $all         = self::data();
        $countryCode = strtolower($countryCode);
        if (!isset($all[$countryCode])) {
            $countryCode = 'us';
        }
        $dataset = $all[$countryCode];

        // Resolve gender
        if ($gender === 'random') {
            $gender = (rand(0, 1) === 0) ? 'male' : 'female';
        }
        if (!isset(self::$names[$gender])) {
            $gender = 'male';
        }

        // Pick name
        $firstNames = self::$names[$gender]['first'];
        $lastNames  = self::$names[$gender]['last'];
        $firstName  = $firstNames[array_rand($firstNames)];
        $lastName   = $lastNames[array_rand($lastNames)];

        // Pick city, optionally filtered by state
        $cities = $dataset['cities'];
        if ($state) {
            $filtered = array_filter($cities, function ($c) use ($state) {
                return stripos($c['state'], $state) !== false;
            });
            if (!empty($filtered)) {
                $cities = array_values($filtered);
            }
        }
        $cityData = $cities[array_rand($cities)];

        $streetNum = rand(1, 9999);
        $street    = $dataset['streets'][array_rand($dataset['streets'])];
        $zip       = self::makeZip($countryCode, $cityData['zip_prefix']);
        $phone     = self::makePhone($countryCode);

        return [
            'name'         => $firstName . ' ' . $lastName,
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'gender'       => ucfirst($gender),
            'street'       => $streetNum . ' ' . $street,
            'city'         => $cityData['city'],
            'state'        => $cityData['state'],
            'zip'          => $zip,
            'country'      => $dataset['country'],
            'country_code' => $dataset['code'],
            'phone'        => $phone,
            'email'        => strtolower($firstName . '.' . $lastName . rand(10, 999) . '@example.com'),
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Log a generation event to the database.
     */
    public static function log(array $address): void
    {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $ip  = get_ip();
            $geo = geolocate_ip($ip);

            $stmt = get_db()->prepare(
                'INSERT INTO generation_logs
                    (session_id, ip_address, country, region, city, country_code, generated_country, generated_data)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                session_id(),
                $ip,
                $geo['country']     ?? null,
                $geo['regionName']  ?? null,
                $geo['city']        ?? null,
                $geo['countryCode'] ?? null,
                $address['country_code'],
                json_encode($address),
            ]);
        } catch (Throwable $e) {
            error_log('Generation log failed: ' . $e->getMessage());
        }
    }

    /**
     * Return a list of all supported countries.
     */
    public static function countries(): array
    {
        $out = [];
        foreach (self::data() as $code => $d) {
            $out[] = ['code' => $code, 'name' => $d['country']];
        }
        return $out;
    }

    /**
     * Get unique states/regions for a given country code.
     */
    public static function states(string $countryCode): array
    {
        $all         = self::data();
        $countryCode = strtolower($countryCode);
        if (!isset($all[$countryCode])) {
            return [];
        }
        $states = [];
        foreach ($all[$countryCode]['cities'] as $c) {
            $states[$c['state']] = $c['state'];
        }
        return array_values($states);
    }
}
