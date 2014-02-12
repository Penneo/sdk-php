<?php
namespace Penneo\SDK;

use Penneo\SDK\ApiConnector;
use Penneo\SDK\CaseFile;
use Penneo\SDK\Signer;

class Document
{
	protected $id;
	protected $caseFile;

	public function __construct($document, $title, CaseFile $caseFile=null, $type = 'signableDocument', $metaData=null, $options=null)
	{
		if (!$caseFile) {
			// Create a case file for the document to exist in.
			$caseFile = new CaseFile($title);
		}
		$this->caseFile = $caseFile;
	
		$this->id = ApiConnector::createDocument($document, $title, $this->caseFile->getId(), $type, $metaData, $options);
		if (!$this->id) throw new \Exception('Penneo: Could not create the document');
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function addSigner(Signer $signer, $role=null, $conditions=null, $signOrder=null)
	{
		return ApiConnector::addSignerToDocument($this->id, $signer->getId(), $role, $conditions, $signOrder);
	}

	public function createSigningRequest(Signer $signer, $sendEmail=false, $emailText=null, $successUrl=null, $failUrl=null)
	{
		$options = array();
		$options['deliverByEmail'] = $sendEmail;
		if ($signer->getEmail()) $options['email'] = $signer->getEmail();
		if ($emailText) $options['emailText'] = $emailText;
		if ($successUrl) $options['successUrl'] = $successUrl;
		if ($failUrl) $options['failUrl'] = $failUrl;
		
		return ApiConnector::getSigningRequest($signer->getId(), $this->caseFile->getId(), $options);
	}
}