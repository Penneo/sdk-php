<?php

namespace Penneo\SDK;

/**
 * Document attached to a case file (Penneo REST: `/documents`, `/documents/{id}`, `/documents/{id}/content`, …).
 *
 * Upload today is PDF-only; `setFile()` maps to the API `file` body field (OpenAPI: form `file` or legacy `pdfFile`).
 * Download uses `GET .../documents/{id}/content` with optional query `signed`
 * (SDK sends `signed=false` when needed; API `decrypt` is left at default).
 *
 * @method static Document find(int|string $id)
 * @method static Document[] findAll()
 * @method static Document[] findBy(
 *     array $criteria,
 *     ?array $orderBy = null,
 *     ?int $limit = null,
 *     ?int $offset = null
 * )
 * @method static Document[] findOneBy(array $criteria, ?array $orderBy = null)
 * @method static Document[] findByTitle(
 *     string $title,
 *     ?array $orderBy = null,
 *     ?int $limit = null,
 *     ?int $offset = null
 * )
 * @method static Document[] findOneByTitle(string $title, ?array $orderBy = null)
 * @method static Document[] findByMetaData(
 *     string $metaData,
 *     ?array $orderBy = null,
 *     ?int $limit = null,
 *     ?int $offset = null
 * )
 * @method static Document[] findOneByMetaData(string $metaData, ?array $orderBy = null)
 * @method static void persist(Document $object)
 * @method static void delete(Document $object)
 */
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

    /** @var SignatureLine[]|null */
    protected $signatureLines = null;

    /**
     * @param CaseFile|null $caseFile Case file this document belongs to (required for create).
     */
    public function __construct(?CaseFile $caseFile = null)
    {
        $this->caseFile = $caseFile;
    }

    /**
     * Case file that owns this document.
     *
     * @return CaseFile
     *
     * @throws Exception
     */
    public function getCaseFile()
    {
        if (!$this->caseFile) {
            $caseFiles = parent::getLinkedEntities($this, CaseFile::class);
            $first = $caseFiles[0] ?? null;
            if (!$first instanceof CaseFile) {
                throw new Exception('Penneo: Case file not found for document');
            }
            $this->caseFile = $first;
        }
        return $this->caseFile;
    }

    /**
     * Signature lines defined on this document.
     *
     * @return SignatureLine[]
     *
     * @throws Exception
     */
    public function getSignatureLines()
    {
        if ($this->signatureLines !== null) {
            return $this->signatureLines;
        }
        return parent::getLinkedEntities($this, SignatureLine::class);
    }

    /**
     * Find a signature line by id on this document.
     *
     * @param int|string $id Signature line id
     *
     * @return SignatureLine|false|null|static Null when not found in the cached list; null when API returns no entity
     *
     * @throws Exception
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
     * Raw document bytes from `GET /documents/{id}/content`.
     * Response is binary when the client does not negotiate `Accept: application/json`.
     *
     * Upload is PDF-only in the API today; this returns whatever bytes the API stores for the document.
     * Content is always returned decrypted (API default); the encrypted storage blob is not exposed via the SDK.
     *
     * @param bool $signed Get the signed version when available (default: true). Pass false for the original document.
     *
     * @return string Binary response body
     *
     * @throws Exception When the HTTP request fails or the SDK returns no response
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
     * @param bool $signed Get the signed version when available (default: true). Pass false for the original document.
     *
     * @return string Raw binary PDF content
     * @deprecated Use {@see self::getContent()} instead.
     *
     */
    public function getPdf(bool $signed = true): string
    {
        return $this->getContent($signed);
    }

    /**
     * Mark the document as signable (`type`: `signable`) before {@see Entity::persist()}.
     *
     * @return void
     */
    public function makeSignable()
    {
        $this->type = 'signable';
    }

    /**
     * Local path to the file; encoded as base64 in the JSON `file` field on create (OpenAPI form field `file`).
     *
     * @param string $filePath Path to the local PDF file supported by the API today
     *
     * @return void
     */
    public function setFile(string $filePath): void
    {
        $this->file = $filePath;
    }

    /**
     * @param string $pdfFile Path to a readable PDF file
     *
     * @return void
     * @deprecated Use {@see self::setFile()} instead (same JSON key `pdfFile` on the wire).
     *
     */
    public function setPdfFile($pdfFile)
    {
        $this->pdfFile = $pdfFile;
    }

    /**
     * Document format from the API numeric `format` field, mapped to a short string when known.
     *
     * Typical value today: `"pdf"`. Unknown integer codes fall back to `(string) $format`.
     *
     * @return string|null Mapped label such as `pdf`, `xml`, `xhtml`, `zip`, or null if not hydrated
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

        return $formats[$this->format] ?? (string)$this->format;
    }

    /**
     * External identifier stamped on document pages (API: `documentId`).
     *
     * @return int|string|null
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * @param string|null $meta
     *
     * @return void
     */
    public function setMetaData($meta)
    {
        $this->metaData = $meta;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return new \DateTime('@' . $this->created);
    }

    /**
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return new \DateTime('@' . $this->modified);
    }

    /**
     * @return \DateTime
     */
    public function getCompletedAt()
    {
        return new \DateTime('@' . $this->completed);
    }

    /**
     * Human-readable lifecycle status derived from the API numeric `status` field.
     *
     * @return 'new'|'pending'|'rejected'|'deleted'|'signed'|'completed'
     */
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
     * Document options / opts JSON string as stored by the API.
     *
     * @return string|null
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Linked document type (lazy-loaded from `.../documenttype`).
     *
     * @return DocumentType|null
     *
     * @throws Exception
     */
    public function getDocumentType()
    {
        if ($this->id && !$this->documentType) {
            $documentTypes = parent::getLinkedEntities($this, DocumentType::class);
            $this->documentType = $documentTypes[0];
        }
        return $this->documentType;
    }

    /**
     * @param DocumentType $type
     *
     * @return void
     */
    public function setDocumentType(DocumentType $type)
    {
        $this->documentType = $type;
    }
}
