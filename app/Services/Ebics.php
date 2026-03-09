<?php

namespace App\Services;

use EbicsApi\Ebics\Contexts\BTDContext;
use EbicsApi\Ebics\Contracts\EbicsResponseExceptionInterface;
use EbicsApi\Ebics\EbicsBankLetter;
use EbicsApi\Ebics\Models\Keyring;
use EbicsApi\Ebics\Orders\BTD;
use EbicsApi\Ebics\Orders\HIA;
use EbicsApi\Ebics\Orders\HPB;
use EbicsApi\Ebics\Orders\INI;
use EbicsApi\Ebics\Services\FileKeyringManager;
use EbicsApi\Ebics\Models\Bank;
use EbicsApi\Ebics\Models\User;
use EbicsApi\Ebics\EbicsClient;
use EbicsApi\Ebics\Models\X509\BankX509Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Represents the Ebics protocol integration for handling secure file transfers and transactions.
 *
 * This class provides methods and functionality for interacting with the Ebics standard.
 * It serves as the core component to facilitate communication with banks using the Ebics protocol.
 */
final class Ebics
{
    /** @var string */
    private string $keyringPath;

    /** @var \EbicsApi\Ebics\EbicsClient */
    private EbicsClient $client;

    /** @var \EbicsApi\Ebics\Services\FileKeyringManager */
    private FileKeyRingManager $keyringManager;

    /** @var \EbicsApi\Ebics\Models\Keyring */
    private Keyring $keyring;

    /**
     * Construct Ebics service
     *
     * @param  string $url
     * @param  string $version
     * @param  string $hostId
     * @param  string $partnerId
     * @param  string $userId
     * @param  string $keyringPassword
     * @param  string $keyringPath
     * @throws \EbicsApi\Ebics\Exceptions\EbicsException
     */
    public function __construct(
        string $url,
        string $version,
        string $hostId,
        string $partnerId,
        string $userId,
        string $keyringPassword,
        string $keyringPath,
    ) {
        $this->keyringPath = $keyringPath;
        $this->keyringManager = new FileKeyringManager();

        // Keyring
        if (is_readable($this->keyringPath)) {
            $this->keyring = $this->keyringManager->loadKeyring(
                $this->keyringPath,
                $keyringPassword,
                $version,
            );
        } elseif (is_writable($this->keyringPath)) {
            $this->keyring = $this->keyringManager->createKeyring($version);
            $this->keyring->setPassword($keyringPassword);
        } else {
            throw new RuntimeException("The keyring path isn't accessible : $this->keyringPath");
        }

        // Bank
        $bank = new Bank($hostId, $url);

        // En EBICS 3.0 (et banques françaises), les échanges sont certifiés
        // Le BankX509Generator gère automatiquement les certificats X002/E002
        // que la banque vous a transmis — ils seront vérifiés lors du HPB
        if ($version === Keyring::VERSION_30) {
            $certificateGenerator = new BankX509Generator();
            $certificateGenerator->setCertificateOptionsByBank($bank);
            $this->keyring->setCertificateGenerator($certificateGenerator);
        }

        // User
        $user = new User($partnerId, $userId);

        // EBICS client
        $this->client = new EbicsClient($bank, $user, $this->keyring);

        // Save keyring if it doesn't exist
        if (!file_exists($this->keyringPath)) {
            $this->client->createUserSignatures();
            $this->saveKeyring();

        }
    }

    /**
     * Save keyring to file
     */
    private function saveKeyring(): void
    {
        $this->keyringManager->saveKeyring(
            $this->client->getKeyring(),
            $this->keyringPath,
        );
    }

    // -------------------------------------------------------------------------
    // Initialize keys and PDF letter
    // -------------------------------------------------------------------------

    // Step 1: Generate and send your keys to bank
    public function sendClientKeys(): void
    {
        try {
            $this->client->executeStandardOrder(new INI());
            $this->saveKeyring();
        } catch (EbicsResponseExceptionInterface $e) {
            throw new \RuntimeException("INI failed [{$e->getResponseCode()}] : {$e->getMeaning()}", 0, $e);
        }

        try {
            $this->client->executeStandardOrder(new HIA());
            $this->saveKeyring();
        } catch (EbicsResponseExceptionInterface $e) {
            throw new \RuntimeException("HIA failed [{$e->getResponseCode()}] : {$e->getMeaning()}", 0, $e);
        }


    }

    // Step 2: Generate the bank letter to send as a confirmation to the bank
    public function generateInitLetter(): void
    {
        $ebicsBankLetter = new EbicsBankLetter();

        $bankLetter = $ebicsBankLetter->prepareBankLetter(
            $this->client->getBank(),
            $this->client->getUser(),
            $this->client->getKeyring(),
        );

        // Save the PDF
        $store = Storage::put(
            dirname($this->keyringPath) . '/bank_letter.pdf',
            $ebicsBankLetter->formatBankLetter(
                $bankLetter,
                $ebicsBankLetter->createPdfBankLetterFormatter(),
            )
        );

        if ($store === false) {
            throw new RuntimeException('Couldn\'t save the bank letter PDF');
        }
    }

    /**
     * Step 3 (After bank validation): retrieve the bank's public keys (HPB).
     * X002/E002 certificates provided by the bank are verified.
     */
    public function fetchBankKeys(): void
    {
        try {
            $this->client->executeInitializationOrder(new HPB());
            $this->saveKeyring();
        } catch (EbicsResponseExceptionInterface $e) {
            throw new \RuntimeException("HPB échoué [{$e->getResponseCode()}] : {$e->getMeaning()}", 0, $e);
        }
    }

    // -------------------------------------------------------------------------
    // Bank statements — camt.053 (BTD on EBICS 3.0)
    // -------------------------------------------------------------------------
    public function getStatements(Carbon $from, Carbon $to): Collection
    {
        // On EBICS 3.0, C53 uses BTD with the service name EOP
        $ctx = new BTDContext();
        $ctx->setServiceName('EOP');
        $ctx->setMsgName('camt.053');
        $ctx->setScope('GLB');
        $ctx->setParserFormat(EbicsClient::FILE_PARSER_FORMAT_XML_FILES);

        try {
            $trx = [];
            $response = $this->client->executeDownloadOrder(new BTD($ctx, $from, $to));

            foreach ($response->getDataFiles() as $file) {
                $trx[] = json_decode(json_encode(simplexml_load_string($file->getContent())));
            }

            return collect($trx);
        } catch (EbicsResponseExceptionInterface $e) {
            throw new RuntimeException("BTD/C53 failed [{$e->getResponseCode()}] : {$e->getMeaning()}", 0, $e);
        }
    }
}
