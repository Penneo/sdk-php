<?php

namespace Penneo\SDK;

class Signer extends Entity
{
    protected static $propertyMapping = array(
        'create' => array(
            'name',
            'socialSecurityNumberPlain',
            'ssnType',
            'vatin',
            'onBehalfOf',
            'storeAsContact'
        ),
        'update' => array(
            'name',
            'socialSecurityNumberPlain',
            'ssnType',
            'vatin',
            'onBehalfOf',
            'storeAsContact'
        )
    );
    protected static $relativeUrl = 'signers';

    /** @var string */
    protected $name;
    /** @var string|null */
    protected $validatedName;
    /** @var string|null */
    protected $socialSecurityNumberPlain;
    /** @var string */
    protected $ssnType = 'legacy';
    /** @var string|null */
    protected $onBehalfOf;
    /** @var string|null */
    protected $vatin;
    /** @var bool|null */
    protected $storeAsContact;


    /** @var CaseFile */
    protected $caseFile;
    /** @var SigningRequest|null */
    protected $signingRequest = null;

    /**
     * @param CaseFile|SignatureLine $parent
     */
    public function __construct($parent)
    {
        $this->caseFile = null;
        if ($parent instanceof CaseFile) {
            $this->caseFile = $parent;
        } elseif ($parent instanceof SignatureLine) {
            $this->caseFile = $parent->getParent()->getCaseFile();
        }
    }

    /**
     * @return CaseFile|null
     */
    public function getParent(): ?CaseFile
    {
        return $this->caseFile;
    }

    /**
     * @return SigningRequest
     */
    public function getSigningRequest(): ?SigningRequest
    {
        if ($this->signingRequest) {
            return $this->signingRequest;
        }
        $requests = parent::getLinkedEntities($this, SigningRequest::class);
        return $requests[0];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getValidatedName(): ?string
    {
        return $this->validatedName;
    }

    public function getSocialSecurityNumber(): ?string
    {
        return $this->socialSecurityNumberPlain;
    }

    public function setSocialSecurityNumber(string $ssn, string $ssnType = 'legacy')
    {
        $this->socialSecurityNumberPlain = $ssn;
        $this->ssnType = $ssnType;
    }

    /**
     *
     * @param string $ssnType
     */
    public function setSsnType(string $ssnType): void
    {
        $this->ssnType = $ssnType;
    }

    public function getSsnType(): string
    {
        return $this->ssnType;
    }

    public function getVATIdentificationNumber(): ?string
    {
        return $this->vatin;
    }

    public function setVATIdentificationNumber(string $vatin)
    {
        $this->vatin = $vatin;
    }

    public function getOnBehalfOf(): ?string
    {
        return $this->onBehalfOf;
    }

    public function setOnBehalfOf(string $onBehalfOf)
    {
        $this->onBehalfOf = $onBehalfOf;
    }

    /**
     * @return bool|null
     */
    public function getStoreAsContact(): ?bool
    {
        return $this->storeAsContact;
    }

    /**
     * @param bool|null $storeAsContact
     */
    public function setStoreAsContact(?bool $storeAsContact): void
    {
        $this->storeAsContact = $storeAsContact;
    }

    public function addSignerType(SignerType $type): bool
    {
        return parent::linkEntity($this, $type);
    }

    public function removeSignerType(SignerType $type): bool
    {
        return parent::unlinkEntity($this, $type);
    }

    /**
     * @return SignerType[]
     */
    public function getSignerTypes()
    {
        return parent::getLinkedEntities($this, SignerType::class);
    }

    /**
     * @return LogEntry[]
     */
    public function getEventLog()
    {
        return parent::getLinkedEntities($this, LogEntry::class);
    }
}
