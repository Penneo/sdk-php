<?php
namespace Penneo\SDK;

class CaseFile extends Entity
{
    const DISPLAY_MODE_TABBED  = 0;
    const DISPLAY_MODE_FLOW    = 1;

    protected static $propertyMapping = array(
        'create' => array(
            'title',
            'metaData',
            'sendAt',
            'expireAt',
            'visibilityMode',
            'documentDisplayMode',
            'caseFileTypeId' => 'caseFileType->getId',
            'sensitiveData',
            'disableNotificationsOwner',
            'reference',
        ),
        'update' => array(
            'title',
            'metaData',
            'caseFileTypeId' => 'caseFileType->getId',
            'visibilityMode',
            'documentDisplayMode',
            'disableNotificationsOwner',
            'sensitiveData',
            'reference',
        )
    );
    protected static $relativeUrl = 'casefiles';

    protected $title;
    protected $metaData;
    protected $sendAt;
    protected $expireAt;
    protected $visibilityMode;
    protected $documentDisplayMode;
    protected $reference;
    protected $sensitiveData;
    protected $disableNotificationsOwner;
    protected $status;
    protected $created;
    protected $signIteration;
    protected $caseFileType;
    
    protected $documents = null;
    protected $signers = null;

    public function __construct()
    {
        // Set default visibility mode
        $this->visibilityMode = 0;
        $this->sensitiveData = false;
        $this->disableNotificationsOwner = false;
        $this->documentDisplayMode = self::DISPLAY_MODE_TABBED;
    }

    public function getCaseFileTemplates()
    {
        return parent::getLinkedEntities($this, 'Penneo\SDK\CaseFileTemplate', 'casefile/casefiletypes');
    }
    
    public function getDocumentTypes()
    {
        if (!$this->id) {
            return array();
        }
        return parent::getLinkedEntities($this, 'Penneo\SDK\DocumentType', 'casefiles/'.$this->id.'/documenttypes');
    }

    public function getSignerTypes()
    {
        if (!$this->id) {
            return array();
        }
        return parent::getLinkedEntities($this, 'Penneo\SDK\SignerType', 'casefiles/'.$this->id.'/signertypes');
    }

    public function getDocuments()
    {
        if ($this->documents !== null) {
            return $this->documents;
        }
        return parent::getLinkedEntities($this, 'Penneo\SDK\Document');
    }

    public function getSigners()
    {
        if ($this->signers !== null) {
            return $this->signers;
        }
        return parent::getLinkedEntities($this, 'Penneo\SDK\Signer');
    }
    
    public function findSigner($id)
    {
        if ($this->signers !== null) {
            foreach ($this->signers as $signer) {
                if ($signer->getId() == $id) {
                    return $signer;
                }
            }
            return null;
        }
        return parent::findLinkedEntity($this, 'Penneo\SDK\Signer', $id);
    }

    public function getErrors()
    {
        return parent::getAssets($this, 'errors');
    }

    public function activate()
    {
        return parent::callAction($this, 'activate');
    }

    public function send()
    {
        return parent::callAction($this, 'send');
    }

    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    public function getMetaData()
    {
        return $this->metaData;
    }
    
    public function setMetaData($meta)
    {
        $this->metaData = $meta;
    }
    
    public function getSendAt()
    {
        return new \DateTime('@'.$this->sendAt);
    }
    
    public function setSendAt(\DateTime $sendAt)
    {
        $this->sendAt = $sendAt->getTimestamp();
    }

    public function getExpireAt()
    {
        return new \DateTime('@'.$this->expireAt);
    }
    
    public function setExpireAt(\DateTime $expireAt)
    {
        $this->expireAt = $expireAt->getTimestamp();
    }

    public function getVisibilityMode()
    {
        return $this->visibilityMode;
    }
    
    public function setVisibilityMode($visibilityMode)
    {
        $this->visibilityMode = $visibilityMode;
    }

    /**
     * Set documentDisplayMode
     *
     * @param integer $documentDisplayMode
     * @return CaseFile
     */
    public function setDocumentDisplayMode($documentDisplayMode)
    {
        $this->documentDisplayMode = $documentDisplayMode;

        return $this;
    }

    /**
     * Get documentDisplayMode
     *
     * @return integer
     */
    public function getDocumentDisplayMode()
    {
        return $this->documentDisplayMode;
    }

    /**
     * Set sensitiveData
     *
     * @param boolean $sensitive
     * @return CaseFile
     */
    public function setSensitiveData($sensitive)
    {
        $this->sensitiveData = $sensitive;

        return $this;
    }

    /**
     * Get sensitiveData
     *
     * @return boolean
     */
    public function getSensitiveData()
    {
        return $this->sensitiveData;
    }

    /**
     * Set disableNotificationsOwner
     *
     * @param boolean $disableNotificationsOwner
     * @return CaseFile
     */
    public function setDisableNotificationsOwner($disableNotificationsOwner)
    {
        $this->disableNotificationsOwner = $disableNotificationsOwner;

        return $this;
    }

    /**
     * Get disableNotificationsOwner
     *
     * @return boolean
     */
    public function getDisableNotificationsOwner()
    {
        return $this->disableNotificationsOwner;
    }

    public function getStatus()
    {
        switch ($this->status) {
            case 0:
                return 'new';
            case 1:
                return 'pending';
            case 2:
                return 'rejected';
            case 3:
                return 'deleted';
            case 4:
                return 'signed';
            case 5:
                return 'completed';
        }
    
        return 'deleted';
    }

    /**
     * Set reference
     *
     * @param string $reference
     * @return CaseFile
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    public function getCreatedAt()
    {
        return new \DateTime('@'.$this->created);
    }
    
    public function getSignIteration()
    {
        return $this->signIteration;
    }

    public function getCaseFileTemplate()
    {
        if ($this->id && !$this->caseFileType) {
            $caseFileTypes = parent::getLinkedEntities($this, 'Penneo\SDK\CaseFileTemplate');
            $this->caseFileType = $caseFileTypes[0];
        }
        return $this->caseFileType;
    }

    public function setCaseFileTemplate(CaseFileTemplate $template)
    {
        $this->caseFileType = $template;
    }
}
