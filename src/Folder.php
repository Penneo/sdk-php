<?php

namespace Penneo\SDK;

class Folder extends Entity
{
    protected static $propertyMapping = array(
        'create' => array('title'),
        'update' => array('title')
    );
    protected static $relativeUrl = 'folders';

    protected $title;

    /**
     * @param int|null $page    Page numbers start at 1
     * @param int      $perPage Does nothing if $page is null
     *
     * @return array|bool
     *
     * @throws Exception
     */
    public function getCaseFiles($page = null, $perPage = PHP_INT_MAX)
    {
        $paging = $page !== null ? array('page' => $page, 'per_page' => $perPage) : [];

        return parent::getLinkedEntities($this, CaseFile::class, null, $paging);
    }

    public function addCaseFile(CaseFile $caseFile)
    {
        return parent::linkEntity($this, $caseFile);
    }

    public function removeCaseFile(CaseFile $caseFile)
    {
        return parent::unlinkEntity($this, $caseFile);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }
}
