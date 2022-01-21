<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api\Response;

use DateTime;
use Obud\FioSigner\Api\ResponseException;


class Order
{
    protected string|null $type = null;

    protected DateTime $dateTime;

    protected string|null $sourceAccount = null;

    protected string|null $amount = null;

    protected string|null $currency = null;

    protected DateTime|null $paymentDate = null;

    protected string|null $destinationAccount = null;

    protected string|null $destinationBank = null;

    protected string|null $swift = null;

    protected string|null $message = null;

    protected string|null $note = null;

    protected string|null $variableSymbol = null;

    protected string|null $specificSymbol = null;

    protected string|null $constantSymbol = null;

    protected string|null $beneficiaryName = null;

    protected string|null $beneficiaryStreet = null;

    protected string|null $beneficiaryCity = null;

    protected string|null $beneficiaryCountry = null;


    public function __construct(
        protected int    $id,
        protected string $description,
    )
    {
        $rows = explode(PHP_EOL, $this->description);
        $required = 0;
        foreach ($rows as $row) {
            $cols = explode(': ', $row, 2);
            if (!isset($cols[0], $cols[1])) {
                continue;
            }
            [$name, $data] = $cols;

            if (in_array($name, ['Pokyn č.', 'Order No.'], true)) {
                $required++;
                if ($id !== (int)$data) {
                    throw new ResponseException('The order ID is not equal to the order ID in the order description.');
                }
            } elseif (in_array($name, ['Typ pokynu', 'Order type'], true)) {
                $this->type = $data;
            } elseif (in_array($name, ['Zadáno', 'Entered', 'Zadané'], true)) {
                $required++;
                $this->dateTime = new DateTime($data);
            } elseif (in_array($name, ['Zdrojový účet', 'Source account'], true)) {
                $this->sourceAccount = $data;
            } elseif (in_array($name, ['Množství', 'Amount', 'Množstvo'], true)) {
                $this->amount = $data;
            } elseif (in_array($name, ['Měna', 'Currency', 'Mena'], true)) {
                $this->currency = $data;
            } elseif (in_array($name, ['Datum', 'Entered', 'Date', 'Dátum'], true)) {
                $this->paymentDate = new DateTime($data);
            } elseif (in_array($name, ['Na účet', 'On Account'], true)) {
                $this->destinationAccount = $data;
            } elseif (in_array($name, ['Kód banky', 'Bank code'], true)) {
                $this->destinationBank = $data;
            } elseif (in_array($name, ['Bic kód/SWIFT', 'BIC Code / SWIFT'], true)) {
                $this->swift = $data;
            } elseif (in_array($name, [
                'Zpráva pro příjemce',
                'Message for recipient',
                'Správa pre príjemcu',
                'Info pro příjemce 1',
                'Info for recipient 1',
                'Info pre príjemcu 1',
            ], true)) {
                $this->message = $data;
            } elseif (in_array($name, ['Vaše poznámka', 'Your note', 'Vaša poznámka'], true)) {
                $this->note = $data;
            } elseif (in_array($name, ['Konstantní symbol', 'Constant symbol', 'Konštantný symbol'], true)) {
                $this->constantSymbol = $data;
            } elseif (in_array($name, ['Variabilní symbol', 'Variable symbol', 'Variabilný symbol'], true)) {
                $this->variableSymbol = $data;
            } elseif (in_array($name, ['Specifický symbol', 'Specific symbol', 'Špecifický symbol'], true)) {
                $this->specificSymbol = $data;
            } elseif (in_array($name, ['Jméno příjemce', 'Beneficiary Name', 'Meno príjemcu'], true)) {
                $this->beneficiaryName = $data;
            } elseif (in_array($name, ['Ulice příjemce', 'Beneficiary Street', 'Ulica príjemcu'], true)) {
                $this->beneficiaryStreet = $data;
            } elseif (in_array($name, ['Město příjemce', 'Beneficiary City', 'Mesto príjemcu'], true)) {
                $this->beneficiaryCity = $data;
            } elseif (in_array($name, ['Země příjemce', 'Beneficiary Country', 'Krajina príjemcu'], true)) {
                $this->beneficiaryCountry = $data;
            }
        }

        if ($required < 2) {
            throw new ResponseException('The order description does not contain all mandatory attributes. ' . $description);
        }
    }


    public function getType(): ?string
    {
        return $this->type;
    }


    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }


    public function getSourceAccount(): ?string
    {
        return $this->sourceAccount;
    }


    public function getAmount(): ?string
    {
        return $this->amount;
    }


    public function getCurrency(): ?string
    {
        return $this->currency;
    }


    public function getPaymentDate(): ?DateTime
    {
        return $this->paymentDate;
    }


    public function getDestinationAccount(): ?string
    {
        return $this->destinationAccount;
    }


    public function getDestinationBank(): ?string
    {
        return $this->destinationBank;
    }


    public function getSwift(): ?string
    {
        return $this->swift;
    }


    public function getMessage(): ?string
    {
        return $this->message;
    }


    public function getNote(): ?string
    {
        return $this->note;
    }


    public function getVariableSymbol(): ?string
    {
        return $this->variableSymbol;
    }


    public function getSpecificSymbol(): ?string
    {
        return $this->specificSymbol;
    }


    public function getConstantSymbol(): ?string
    {
        return $this->constantSymbol;
    }


    public function getBeneficiaryName(): ?string
    {
        return $this->beneficiaryName;
    }


    public function getBeneficiaryStreet(): ?string
    {
        return $this->beneficiaryStreet;
    }


    public function getBeneficiaryCity(): ?string
    {
        return $this->beneficiaryCity;
    }


    public function getBeneficiaryCountry(): ?string
    {
        return $this->beneficiaryCountry;
    }


    public function getId(): int
    {
        return $this->id;
    }


    public function getDescription(): string
    {
        return $this->description;
    }


}