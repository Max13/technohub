<?php

namespace App\Models\Accounting;

enum DisputeType : string
{
    case INCORRECT_ACCOUNT = 'IncorrectAccount'; // AC01
    case CLOSED_ACCOUNT = 'ClosedAccount'; // AC04
    case BLOCKED_ACCOUNT = 'BlockedAccount'; // AC06
    case TRANSACTION_FORBIDDEN = 'TransactionForbidden'; // AG01
    case INVALID_OPERATION_CODE = 'InvalidOperationCode'; // AG02
    case INSUFFICIENT_FUNDS = 'InsufficientFunds'; // AM04
    case DUPLICATION = 'Duplication'; // AM05
    case SETTLEMENT_FAILED = 'SettlementFailed'; // ED05
    case NOT_AUTHORIZED = 'NotAuthorized'; // MD01
    case INVALID_MANDATE = 'InvalidMandate'; // MD02
    case DISPUTED_TRANSACTION = 'DisputedTransaction'; // MD06
    case DECEASED = 'Deceased'; // MD07
    case DISPUTED_MANDATE = 'DisputedMandate'; // MS02
    case REASON_NOT_PROVIDED = 'ReasonNotProvided'; // MS03
    case BANK_ID_INCORRECT = 'BankIdIncorrect'; // RC01
    case BANK_SERVICE = 'BankService'; // SL01
    case UNKNOWN = 'Unknown';

    /**
     * Returns the available ISO codes for SEPA rejections
     *
     * @return array<string, array{title: string, description: string, case: self}>
     */
    static protected function availableCodes() : array
    {
        return [
            [
                'code' => 'AC01',
                'title' => 'Coordonnée Bancaire inexploitable',
                'description' => 'Le format de l’IBAN est incorrect. L’IBAN n’existe pas sur les livres de la banque du débiteur',
                'case' => self::INCORRECT_ACCOUNT,
            ],[
                'code' => 'AC04',
                'title' => 'Compte clôturé',
                'description' => 'Compte clôturé',
                'case' => self::CLOSED_ACCOUNT,
            ],[
                'code' => 'AC06',
                'title' => 'Opposition sur compte / Compte bloqué',
                'description' => 'Prélèvement SEPA interdit par le débiteur sur ce compte. Le client a interdit tout prélèvement SEPA au débit de son compte. Le compte est bloqué à la suite d’une décision Judicaire (dépôt de bilan), d’une saisie arrêt ou d’un avis à tiers détenteur ou décisions d’embargo',
                'case' => self::BLOCKED_ACCOUNT,
            ],[
                'code' => 'AG01',
                'title' => 'Opération non admise',
                'description' => 'Pour des raisons réglementaires, ce compte n’est pas éligible au prélèvement et ne peut pas être débité (comptes d’épargne, livret A, CIF, CEL, PEL, LDD, …)',
                'case' => self::TRANSACTION_FORBIDDEN,
            ],[
                'code' => 'AG02',
                'title' => 'Code opération incorrect',
                'description' => 'Le code opération indiqué est incorrect. Le créancier n’a pas respecté le bon séquencement pour la présentation de l’opération, par exemple : Récurrent après un one off',
                'case' => self::INVALID_OPERATION_CODE,
            ],[
                'code' => 'AM04',
                'title' => 'Provision insuffisante',
                'description' => 'La provision sur le compte de paiement du débiteur n’est pas suffisante pour payer le montant total de la transaction.',
                'case' => self::INSUFFICIENT_FUNDS,
            ],[
                'code' => 'AM05',
                'title' => 'Doublon',
                'description' => 'La même opération a déjà été traitée par la banque du débiteur',
                'case' => self::DUPLICATION,
            ],[
                'code' => 'ED05',
                'title' => 'Règlement impossible',
                'description' => 'Le règlement du SCT/SDD/B2B a échoué. (La banque initiatrice du SCT ou la banque du débiteur ou le CSM doit déclarer une défaillance de règlement)',
                'case' => self::SETTLEMENT_FAILED,
            ],[
                'code' => 'MD01',
                'title' => 'Pas d’autorisation / Absence de mandat',
                'description' => 'Le mandat n’existe pas, le débiteur n’a pas signé de mandat ou donné son consentement pour être débité par prélèvement SEPA. Remboursement pour transaction non autorisée (jusqu’à 13 mois après la date de débit du compte). Le mandat est annulé ou révoqué. Le mandat est caduc (selon la règle des 36 mois d’inactivité).',
                'case' => self::NOT_AUTHORIZED,
            ],[
                'code' => 'MD02',
                'title' => 'Donnée mandat incorrecte',
                'description' => 'Les données du mandat de l’opération ne sont pas identiques à celles du mandat. Les amendements n’ont pas été communiqués. La date du mandat n’est pas correcte (ex : date postérieure à la date de règlement de l’opération) Les données du mandat reçues ne sont pas cohérentes avec les données reçues pour la même RUM. Les données amendées du mandat ne sont pas reprises dans l’opération suivante. Les données du mandat amendé sont identiques à celles communiquées initialement.',
                'case' => self::INVALID_MANDATE,
            ],[
                'code' => 'MD06',
                'title' => 'Contestation débiteur / Contestation d’une opération autorisée',
                'description' => 'Désaccord du débiteur quel que soit le motif. Droit à remboursement du débiteur exprimé dans les 8 semaines de la date de règlement.',
                'case' => self::DISPUTED_TRANSACTION,
            ],[
                'code' => 'MD07',
                'title' => 'Titulaire décédé',
                'description' => 'Titulaire décédé',
                'case' => self::DECEASED,
            ],[
                'code' => 'MS02',
                'title' => 'Sur ordre du client bénéficiaire / Refus du débiteur',
                'description' => 'Le bénéficiaire ne souhaite pas être crédité et rejette le virement (SDD - Reversal) Refus de payer du débiteur : Demande du débiteur d’interdire tout prélèvement SEPA correspondant à un couple d’ICS/RUM donné (mise en place d’une opposition par le débiteur).',
                'case' => self::DISPUTED_MANDATE,
            ],[
                'code' => 'MS03',
                'title' => 'Raison non communiquée',
                'description' => 'Raison non communiquée',
                'case' => self::REASON_NOT_PROVIDED,
            ],[
                'code' => 'RC01',
                'title' => 'Identifiant bancaire de la banque destinataire est incorrect',
                'description' => 'Le BIC du débiteur n’est pas correct, soit pour une raison liée au format, soit parce qu’il n’est pas valide (faux)',
                'case' => self::BANK_ID_INCORRECT,
            ],[
                'code' => 'SL01',
                'title' => 'Service spécifique',
                'description' => 'Utilisé dans le domaine de la protection du débiteur, notamment pour des raisons légales liées au règlement EU 260/2012 (article 5).',
                'case' => self::BANK_SERVICE,
            ],
        ];
    }

    /**
     * Returns the rejection type from its ISO code
     *
     * @param  string $code
     * @return self
     */
    static public function fromIso(string $code) : self
    {
        foreach (self::availableCodes() as $data) {
            if ($data['code'] === $code) {
                return $data['case'];
            }
        }

        return self::UNKNOWN;
    }

    /**
     * Returns the rejection from its value
     *
     * @param  string $value
     * @return self
     */
    static public function fromValue(string $value) : self
    {
        foreach (self::availableCodes() as $data) {
            if ($data['case'] === $value) {
                return $data['case'];
            }
        }

        return self::UNKNOWN;
    }

    /**
     * Returns the current DisputeType ISO code
     *
     * @return string
     */
    public function code() : string
    {
        foreach (self::availableCodes() as $data) {
            if ($data['case']->name === $this->name) {
                return $data['code'];
            }
        }

        return '';
    }

    /**
     * Returns the rejection title
     *
     * @return string
     */
    public function title() : string
    {
        foreach (self::availableCodes() as $data) {
            if ($data['case']->name === $this->name) {
                return $data['title'];
            }
        }

        return '';
    }

    /**
     * Returns the rejection description
     *
     * @return string
     */
    public function description() : string
    {
        foreach (self::availableCodes() as $data) {
            if ($data['case']->name === $this->name) {
                return $data['description'];
            }
        }

        return '';
    }
}
