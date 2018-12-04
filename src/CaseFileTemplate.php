<?php
namespace Penneo\SDK;

class CaseFileTemplate extends Entity
{
    protected static $relativeUrl = 'casefiletype';

    protected $name;
    protected $documentTypes;

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return DocumentType[]
     */
    public function getDocumentTypes()
    {
        return $this->documentTypes;
    }
}
