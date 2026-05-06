<?php

namespace Penneo\SDK;

class SignatureLine extends Entity
{
    protected static $propertyMapping = array(
        'create' => array('role','conditions','signOrder'),
        'update' => array('role','conditions','signOrder')
    );
    protected static $relativeUrl = 'signaturelines';

    protected $document;
    /** @var Signer|null */
    protected $signer = null;
    protected $role;
    protected $conditions;
    protected $signOrder = 0;
    protected $signedAt;

    protected $signerId = null;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * @return Document
     */
    public function getParent()
    {
        return $this->document;
    }

    /**
     * @return Signer|null
     */
    public function getSigner(): ?Signer
    {
        if ($this->signer == null) {
            if ($this->signerId !== null) {
                $found = $this->document->getCaseFile()->findSigner($this->signerId);
                $this->signer = $found instanceof Signer ? $found : null;
            } else {
                $signers = parent::getLinkedEntities($this, Signer::class);
                $first = $signers[0] ?? null;
                $this->signer = $first instanceof Signer ? $first : null;
            }
        }

        return $this->signer;
    }

    public function setSigner(Signer $signer)
    {
        $this->signer = $signer;
        return parent::linkEntity($this, $signer);
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role): void
    {
        $this->role = $role;
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function setConditions($conditions): void
    {
        $this->conditions = $conditions;
    }

    public function getSignOrder()
    {
        return $this->signOrder;
    }

    public function setSignOrder($signOrder): void
    {
        $this->signOrder = $signOrder;
    }

    public function getSignedAt(): ?\DateTime
    {
        if ($this->signedAt) {
            return new \DateTime('@' . $this->signedAt);
        }
        return null;
    }
}
