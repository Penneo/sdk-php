<?php
namespace Penneo\SDK;

class ApiConnector
{
	static protected $clientKey;
	static protected $clientSecret;
	static protected $endpoint = 'https://sandbox.penneo.com/api';
	static protected $lastError;
	
	/**
	 * Initialize the API connector class.
	 *
	 * @param string $key        Your Penneo API key
	 * @param string $secret     Your Penneo API secret
	 * @param string $endpoint   The API endpoint url. This defaults to the API sandbox.
	 */
	public static function initialize($key, $secret, $endpoint=null)
	{
		self::$clientKey = $key;
		self::$clientSecret = $secret;
		if ($endpoint) self::$endpoint = $endpoint;
	}
	
	/**
	 * Creates a new case file.
	 *
	 * @param string $title      A searchable string naming the case file
	 * @param string $metaData   A searchable string for categorizing the case file.
	 *
	 * @return mixed   The ID of the new case file or false if creation failed.
	 */
	public static function createCaseFile($title, $metaData=null)
	{
		$data = array(
			'clientKey' => self::$clientKey,
			'title' => $title
		);
		if ($metaData) $data['metaData'] = $metaData;
		$res = self::callServer($data, 'createCaseFile');
		if (!$res) return false;
		return intval($res['id']);
	}

	/**
	 * Creates a new document.
	 *
	 * @param string $document      The text contents of a PDF document
	 * @param string $title         A searchable string naming the document.
	 * @param integer $caseFileId   The ID of the case file to map this document to.
	 * @param string $type          Can be 'signableDocument' or 'attachment'.
	 * @param string $metaData      A searchable string for categorizing the document.
	 * @param array $options        Refer to the API documentation.
	 *
	 * @return mixed   The ID of the new document or false if creation failed.
	 */
	public static function createDocument($document, $title, $caseFileId, $type = 'signableDocument', $metaData=null, $options=array())
	{
		$data = array(
			'clientKey' => self::$clientKey,
			'document' => base64_encode($document),
			'title' => $title,
			'caseFileId' => intval($caseFileId),
			'type' => $type
		);
		if ($metaData) $data['metaData'] = $metaData;
		if ($options) $data['options'] = $options;
		
		$res = self::callServer($data, 'createDocument');
		if (!$res) return false;
		return intval($res['id']);
	}

	/**
	 * Creates a new signer.
	 *
	 * @param string $name                   The full name of the signer.
	 * @param string $onBehalfOf             Specify if the signer signs the document on behalf of a company or an organization.
	 * @param string $socialSecurityNumber   The social security number of the signer
	 *
	 * @return mixed   The ID of the new signer or false if creation failed.
	 */
	public static function createSigner($name, $onBehalfOf=null, $socialSecurityNumber=null)
	{
		$data = array(
			'clientKey' => self::$clientKey,
			'name' => $name
		);
		if ($onBehalfOf) $data['onBehalfOf'] = $onBehalfOf;
		if ($socialSecurityNumber) $data['socialSecurityNumber'] = $socialSecurityNumber;
		
		$res = self::callServer($data, 'createSigner');
		if (!$res) return false;
		return intval($res['id']);
	}

	/**
	 * Method to map a signer to a document.
	 *
	 * @param integer $documentId
	 * @param integer $signerId
	 * @param string $role
	 * @param string $conditions
	 * @param integer $signOrder
	 *
	 * @return boolean   Indicates if the operation succeeded or not.
	 */
	public static function addSignerToDocument($documentId, $signerId, $role=null, $conditions=null, $signOrder=null)
	{
		$data = array(
			'clientKey' => self::$clientKey,
			'documentId' => intval($documentId),
			'signerId' => intval($signerId),
		);
		if ($role) $data['role'] = $role;
		if ($conditions) $data['conditions'] = $conditions;
		if ($signOrder) $data['signOrder'] = $signOrder;
	
		$res = self::callServer($data, 'addSignerToDocument');
		if (!$res) return false;
		return $res['status'];
	}
	
	/**
	 * Method to create a new signing request.
	 *
	 * Available options:
	 *   ['deliverByEmail']        Deliver the link to the signing portal by email. This requires the email option to be set.
	 *   ['email']                 Signers email address.
	 *   ['emailText']             A message delivered to the signer in the email. If not set, the Penneo default will be used.
	 *   ['successUrl']            A URL to return the signer to, once the signing process is complete.
	 *   ['failUrl']               A URL to return the signer to, if the signer rejects to sign.
	 *
	 * @param integer $signerId    Signer ID
	 * @param integer $caseFileId  Case file ID
	 * @param mixed[] $options     Array of options
	 *
	 * @return string  Link to the Penneo signing portal.
	 */
	public static function getSigningRequest($signerId, $caseFileId, $options=array())
	{
		$options['clientKey'] = self::$clientKey;
		$options['signerId'] = intval($signerId);
		$options['caseFileId'] = intval($caseFileId);

		$res = self::callServer($options, 'getSigningRequest');
		if (!$res) return false;
		return $res['signingUrl'];
	}

	/**
	 * Using this method you can search your Penneo case files by either title or meta data.
	 *
	 * @param string $titleQuery   Return case files with titles matching this partial string.
	 * @param string $metaQuery    Return case files with meta data matching this partial string.
	 *
	 * @return mixed[]  An array of case files matching the queries. See the getCaseFile method in the API documentation for details.
	 */
	public static function getCaseFiles($titleQuery=null, $metaQuery=null)
	{
		$options = array(
			'clientKey' => self::$clientKey,
			'titleQuery' => $titleQuery,
			'metaQuery' => $metaQuery
		);
		
		$res = self::callServer($options, 'getCaseFiles');
		if (!$res) return false;
		return $res['caseFiles'];
	}
	
	/**
	 * Method for retrieving a single case file.
	 *
	 * @param integer $id   The ID of the case file to retrieve
	 *
	 * @return mixed[]  See API documentation for details
	 */
	public static function getCaseFile($id)
	{
		$options = array(
			'clientKey' => self::$clientKey,
			'caseFileId' => intval($id)
		);
		
		$res = self::callServer($options, 'getCaseFile');
		if (!$res) return false;
		return $res;
	}

	/**
	 * Using this method you can search your Penneo documents by either title or meta data.
	 *
	 * @param string $titleQuery   Return documents with titles matching this partial string.
	 * @param string $metaQuery    Return documents with meta data matching this partial string.
	 *
	 * @return mixed[]  An array of documents matching the queries. See the getDocument method in the API documentation for details.
	 */
	public static function getDocuments($titleQuery=null, $metaQuery=null)
	{
		$options = array(
			'clientKey' => self::$clientKey,
			'titleQuery' => $titleQuery,
			'metaQuery' => $metaQuery
		);
		
		$res = self::callServer($options, 'getDocuments');
		if (!$res) return false;
		return $res['documents'];
	}
	
	/**
	 * Method for retrieving a singe document.
	 *
	 * @param integer $id           The ID of the document to retrieve
	 * @param boolean $includePdf   Whether to include the PDF in the result.
	 *
	 * @return mixed[]  See API documentation for details
	 */
	public static function getDocument($id, $includePdf=false)
	{
		$options = array(
			'clientKey' => self::$clientKey,
			'documentId' => $id,
			'includePdf' => $includePdf
		);
		
		return self::callServer($options, 'getDocument');
	}

	public static function getLastError()
	{
		return self::$lastError;
	}

	private static function callServer($data, $method)
	{
		ksort($data);
		$signature = hash_hmac('sha1', implode('',$data), self::$clientSecret);
		$request = xmlrpc_encode_request($method, array($signature,$data));
	
		$context = stream_context_create(array('http' => array(
			'method' => 'POST',
			'header' => 'Content-Type: text/xml',
			'content' => $request
		)));
		$file = file_get_contents(self::$endpoint, false, $context);
		$response = xmlrpc_decode($file);
	
		if ($response && is_array($response) && xmlrpc_is_fault($response)) {
			self::$lastError = 'xmlrpc: '.$response['faultString'].' ('.$response['faultCode'].')';
			return false;
		}
		
		return $response;
	}
}
