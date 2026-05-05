<?php

namespace Penneo\SDK;

class Document extends Entity
{
    protected static $propertyMapping = array(
        'create' => array(
            'caseFileId' => 'caseFile->getId',
            'title',
            'metaData',
            'options',
            'type',
            '@pdfFile',
            '@file',
            'documentTypeId' => 'documentType->getId'
        ),
        'update' => array(
            'title',
            'metaData',
            'options',
            'documentTypeId' => 'documentType->getId'
        )
    );
    protected static $relativeUrl = 'documents';

    protected $documentId;
    protected $title;
    protected $metaData;
    protected $options;
    protected $created;
    protected $modified;
    protected $completed;
    protected $status;
    protected $format;
    protected $pdfFile;
    protected $file;

    protected $caseFile;
    protected $type = 'attachment';
    protected $documentType;

    protected $signatureLines = null;

    public function __construct(?CaseFile $caseFile = null)
    {
        $this->caseFile = $caseFile;
    }

    /**
     * @return CaseFile
     */
    public function getCaseFile()
    {
        if (!$this->caseFile) {
            $caseFiles = parent::getLinkedEntities($this, CaseFile::class);
            $this->caseFile = $caseFiles[0];
        }
        return $this->caseFile;
    }

    /**
     * @return SignatureLine[]
     */
    public function getSignatureLines()
    {
        if ($this->signatureLines !== null) {
            return $this->signatureLines;
        }
        return parent::getLinkedEntities($this, SignatureLine::class);
    }

    /**
     * @param $id
     *
     * @return SignatureLine|false
     */
    public function findSignatureLine($id)
    {
        if ($this->signatureLines !== null) {
            foreach ($this->signatureLines as $signatureLine) {
                if ($signatureLine->getId() == $id) {
                    return $signatureLine;
                }
            }
            return null;
        }
        return parent::findLinkedEntity($this, SignatureLine::class, $id);
    }

    /**
     * Download the document content as raw binary via GET .../content.
     *
     * Upload is PDF-only in the API today; this returns whatever bytes the API stores for the document.
     * Content is always returned decrypted (API default); the encrypted storage blob is not exposed via the SDK.
     *
     * @param bool $signed Get the signed version when available (default: true). Pass false for the original document.
     *
     * @return string Raw binary content
     */
    public function getContent(bool $signed = true): string
    {
        $params = [];
        if (!$signed) {
            $params['signed'] = 'false';
        }

        return parent::getBinaryContent($this, 'content', $params);
    }

    /**
     * @deprecated Use getContent() instead.
     *
     * @param bool $signed Get the signed version when available (default: true). Pass false for the original document.
     *
     * @return string Raw binary PDF content
     */
    public function getPdf(bool $signed = true): string
    {
        return $this->getContent($signed);
    }

    public function makeSignable()
    {
        $this->type = 'signable';
    }

    /**
     * Set the document file from a local path. The file is base64-encoded and sent as the API `file`
     * field (alongside the legacy `pdfFile` from setPdfFile()). Only PDF uploads are supported by
     * the API today; this naming prepares for additional formats without breaking compatibility.
     *
     * @param string $filePath Path to the local PDF file
     */
    public function setFile(string $filePath): void
    {
        $this->file = $filePath;
    }

    /**
     * @deprecated Use setFile() instead.
     */
    public function setPdfFile($pdfFile)
    {
        $this->pdfFile = $pdfFile;
    }

    /**
     * Return the document format as reported by the API.
     *
     * In practice this is `"pdf"` for current integrations. Other numeric format codes from the API
     * are mapped when present; additional format names may apply as the API evolves.
     *
     * @return string|null
     */
    public function getFormat(): ?string
    {
        if ($this->format === null) {
            return null;
        }

        $formats = [
            1 => 'pdf',
            2 => 'xml',
            3 => 'xhtml',
            4 => 'zip',
        ];

        return $formats[$this->format] ?? (string) $this->format;
    }

    public function getDocumentId()
    {
        return $this->documentId;
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

    public function getCreatedAt()
    {
        return new \Datetime('@' . $this->created);
    }

    public function getModifiedAt()
    {
        return new \Datetime('@' . $this->modified);
    }

    public function getCompletedAt()
    {
        return new \Datetime('@' . $this->completed);
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

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return DocumentType
     */
    public function getDocumentType()
    {
        if ($this->id && !$this->documentType) {
            $documentTypes = parent::getLinkedEntities($this, DocumentType::class);
            $this->documentType = $documentTypes[0];
        }
        return $this->documentType;
    }

    public function setDocumentType(DocumentType $type)
    {
        $this->documentType = $type;
    }
}
