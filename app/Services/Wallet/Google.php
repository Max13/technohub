<?php

namespace App\Services\Wallet;

use App\Services\Wallet;
use Firebase\JWT\JWT;
use Google\Client;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Walletobjects;
use Google\Service\Walletobjects\GenericObject;
use Google\Service\Walletobjects\Image;
use Google\Service\Walletobjects\ImageModuleData;
use Google\Service\Walletobjects\ImageUri;
use Google\Service\Walletobjects\LinksModuleData;
use Google\Service\Walletobjects\LocalizedString;
use Google\Service\Walletobjects\MerchantLocation;
use Google\Service\Walletobjects\PassConstraints;
use Google\Service\Walletobjects\TextModuleData;
use Google\Service\Walletobjects\TranslatedString;
use Google\Service\Walletobjects\Uri;

class Google implements Wallet
{
    /** @var array */
    private array $credentials;

    /** @var string */
    protected $issuerId;

    /** @var string */
    protected string $class;

    /** @var \Google\Service\Walletobjects */
    protected Walletobjects $service;

    /**
     * Construct Google Wallet service
     *
     * @param  array|string $credentials  Array or JSON string formatted credentials
     * @param  string       $issuerId
     * @param  string       $class
     */
    public function __construct($credentials, string $issuerId, string $class)
    {
        $this->credentials = is_array($credentials) ? $credentials : json_decode($credentials, true);
        $this->issuerId = $issuerId;
        $this->class = $class;

        // Initialize Google Wallet API service
        $client = new Client;
        $client->setApplicationName(config('app.name'));
        $client->setScopes(Walletobjects::WALLET_OBJECT_ISSUER);
        $client->setAuthConfig($this->credentials);

        $this->service = new Walletobjects($client);
    }

    /**
     * Check if this badge has already been registered to Google
     *
     * @param  string $uuid
     * @return bool
     *
     * @throws \Google\Service\Exception
     */
    public function exists($uuid) : bool
    {
        try {
            $this->service->genericobject->get("{$this->issuerId}.{$uuid}");

            return true;
        } catch (GoogleServiceException $e) {
            if (empty($e->getErrors()) || $e->getErrors()[0]['reason'] != 'resourceNotFound') {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Destroy a badge registered to Google Wallet
     *
     * @param  string  $uuid
     */
    public function destroy($uuid)
    {
        //
    }

    /**
     * Returns token to use with Add to Wallet link
     *
     * @param  \App\Models\User $user
     * @return string
     *
     * @throws \Google\Service\Exception
     */
    public function token($uuid, $fullname, $level, $points) : string
    {
        if ($this->exists($uuid)) {
            $pass = $this->service->genericobject->get("{$this->issuerId}.{$uuid}");
        } else {
            $pass = new GenericObject([
                'id' => "{$this->issuerId}.{$uuid}",
                'classId' => "{$this->issuerId}.{$this->class}",
                // 'genericType' => 'GENERIC_OTHER',
                // 'merchantLocations' => [
                //     new MerchantLocation([
                //         'latitude' => 48.8579342,
                //         'longitude' => 2.3917972,
                //     ]),
                //     new MerchantLocation([
                //         'latitude' => 48.8575968,
                //         'longitude' => 2.3915931,
                //     ]),
                // ],
                // 'passConstraints' => new PassConstraints([
                //     'screenshotEligibility' => 'INELIGIBLE',
                // ]),
                // 'smartTapRedemptionValue' => $uuid,
                'state' => 'ACTIVE',
                'hexBackgroundColor' => '#000000',
                'logo' => new Image([
                    'sourceUri' => new ImageUri([
                        'uri' => 'https://pass.technohub.ovh'.mix('/img/wallet-logo@2x.jpg'),// url(mix('/img/wallet-logo@2x.jpg')),
                    ]),
                    'contentDescription' => new LocalizedString([
                        'defaultValue' => new TranslatedString([
                            'language' => 'fr-FR',
                            'value' => 'ITIC Paris | Tech School'
                        ])
                    ])
                ]),
                'cardTitle' => new LocalizedString([
                    'defaultValue' => new TranslatedString([
                        'language' => 'fr-FR',
                        'value' => 'ITIC Paris | Tech School',
                    ])
                ]),
                // 'subheader' => new LocalizedString([
                //     'defaultValue' => new TranslatedString([
                //         'language' => 'fr-FR',
                //         'value' => 'Étudiant'
                //     ])
                // ]),
                'header' => new LocalizedString([
                    'defaultValue' => new TranslatedString([
                        'language' => 'fr-FR',
                        'value' => $fullname,
                    ]),
                ]),
                'textModulesData' => [
                    new TextModuleData([
                        'id' => 'LEVEL_ID',
                        'header' => __('Level'),
                        'body' => $level,
                    ]),
                    // new TextModuleData([
                    //     'header' => 'Text module header',
                    //     'body' => 'Text module body',
                    //     'id' => 'TEXT_MODULE_ID'
                    // ]),
                    new TextModuleData([
                        'id' => 'POINTS_ID',
                        'header' => __('Points'),
                        'body' => $points,
                    ]),
                ],
                // 'barcode' => new Barcode([
                //     'type' => 'QR_CODE',
                //     'value' => 'QR code value'
                // ]),
                'linksModuleData' => new LinksModuleData([
                    'uris' => [
                        new Uri([
                            'uri' => 'http://maps.google.com/',
                            'description' => 'Link module URI description',
                            'id' => 'LINK_MODULE_URI_ID'
                        ]),
                        new Uri([
                            'uri' => 'tel:6505555555',
                            'description' => 'Link module tel description',
                            'id' => 'LINK_MODULE_TEL_ID'
                        ])
                    ]
                ]),
                'heroImage' => new Image([
                    'sourceUri' => new ImageUri([
                        'uri' => 'https://pass.technohub.ovh'.mix('/img/wallet-hero.jpg'), // url(mix('/img/wallet-hero.jpg')),
                    ]),
                    // 'contentDescription' => new LocalizedString([
                    //     'defaultValue' => new TranslatedString([
                    //         'language' => 'fr-FR',
                    //         'value' => 'Hero image description'
                    //     ])
                    // ])
                ]),
                // 'imageModulesData' => [
                //     new ImageModuleData([
                //         'mainImage' => new Image([
                //             'sourceUri' => new ImageUri([
                //                 'uri' => 'http://farm4.staticflickr.com/3738/12440799783_3dc3c20606_b.jpg'
                //             ]),
                //             'contentDescription' => new LocalizedString([
                //                 'defaultValue' => new TranslatedString([
                //                     'language' => 'fr-FR',
                //                     'value' => 'Image module description'
                //                 ])
                //             ])
                //         ]),
                //         'id' => 'IMAGE_MODULE_ID'
                //     ])
                // ],
            ]);

            // $pass = $this->service->genericobject->insert($pass);
            // dump($pass);
        }

        return JWT::encode(
            [
                'iss' => $this->credentials['client_email'],
                'aud' => 'google',
                'origins' => [config('app.url')],
                'typ' => 'savetowallet',
                'payload' => [
                    'genericObjects' => [
                        $pass,
                    ],
                ],
            ],
            $this->credentials['private_key'],
            'RS256',
        );
    }
}
