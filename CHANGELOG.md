# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
### Changed
### Removed

## 2.5.2 - 2023-08-17
### Added
- Added support for psr/log versions ^2.0 and ^3.0


## 2.5.1 - 2023-07-13
### Added
- Added support for french `fr` and finnish `fi` language options on casefiles


## 2.5.0 - 2023-05-23
### Added
- Added `insecureSigningMethods` to SigningRequest allowing for finer control over which simple electronic signature methods
should be available to a signer
```php
$signingRequest->setInsecureSigningMethods([InsecureSigningMethods::DRAW, InsecureSigningMethods::IMAGE, InsecureSigningMethods::TEXT]);
```


## 2.4.2 - 2022-12-22
### Fixed
- Fixed "Calling method X on null" issues: https://github.com/Penneo/sdk-php/issues/66


## 2.4.1 - 2022-11-30
### Fixed
- Fixed "never true" if condition and "Calling method X on null" issues: https://github.com/Penneo/sdk-php/pull/63


## 2.4.0 - 2022-10-31
### Added
- Added the `storeAsContact` property to signers


## 2.3.0 - 2021-05-26
### Changed
- Specification of `composer.json` changed to accept `php:^8.0`


## 2.2.0 - 2021-05-04
### Added
- Allowed setting Dutch `nl` as a language on casefiles


## 2.1.2 - 2021-03-22
### Fixed
- Fixed not being able to call ApiConnector::callServer with array data


## [2.1.1] - 2021-03-04
### Fixed
- Fixed a bug that caused case file activation to fail


## [2.1.0] - 2020-11-11
### Added
- Added support for specifying which type of SSN signers should validate as.
```
$signer->setSocialSecurityNumber($ssn, $ssnType); // $ssnType is optional here, and defaults to 'legacy'
$signer->setSsnType($ssnType); // or set it like this

$ssType = $signer->getSsnType();
```
Not setting the SSN type is now deprecated, and will throw an error in the next major version.
See https://app.penneo.com/api/v1/signers/ssn-types for info on permitted values.

- Added support for setting up webhook subscriptions. See
[the docs](https://github.com/Penneo/sdk-net/blob/master/docs/webhooks.md) for more details.


## [2.0.0] - 2020-11-06
### Changed
- PHP 7.2 is now required.

### Breaking changes
- Moved from guzzle/guzzle to guzzlehttp/guzzle, all guzzle options sent to ApiConnector::initialize() should be updated.
- Removed ApiConnector::getLastError(). Use setLogger() instead.
- Removed ApiConnector::enableDebug(). Use setLogger() instead.


## [1.15.0] - 2020-07-15
- Added request ids to relevant log messages
- PN-638 Added support for setting and unsetting the `disableEmailAttachments` flag for CaseFile objects

With this flag set, the emails signers and the case file owner get when case files are finalized will not include the signed PDF files.

Note: you must use a `/v2` endpoint to support this, for example: `https://sandbox.penneo.com/api/v2`.


## [1.14.0] - 2020-05-25
- PN-382 fix - Added missing Customer method `setEmailSignature()`

## [1.13.0] - 2020-05-11 
- PN-382 EmailSignature methods added to EmailSignature entity, to support our automatic data-hydrators
- PN-382 Endpoint to fetch e-mail signature from signing service updated to reflect corret URL

## [1.12.1] - 2018-12-18
### Added
- [\#44] Language can be specified for the case file using the `setLanguage()` method
- [\#44] Get the case file Id using the `getId()` method

## [1.11.1] - 2018-12-04
### Fixed
- [\#39] Paging wasn't working properly for case files that belong to a folder

## [1.11.0] - 2018-12-04
### Added
- [\#39] Add a way of specifying extra GET parameters with the model classes' getLinkedEntity() method
- [\#39] Add paging parameters to Folder::getCaseFiles()
- [\#39] Methods will now throw Penneo\Exception instead of \Exception
- [\#39] Change the minimum required php version from 5.3 to 5.4
- [\#39] Add ext-json to the composer requirements

## [1.10.0] - 2018-09-05
### Added
- [\#37] Configure reminders and completed emails for signing requests.

## [1.9.1] - 2017-10-24
### Fixed
- [\#33] Updated `psr/log` dependency

## [1.9.0] - 2017-09-25
### Added
- [\#31] Create copy recipients for the signed documents

## [1.8.0] - 2017-09-22
### Added
- [\#29] Case files can now be create and updated with the `disableNotificationsOwner` flag

## [1.7.0] - 2017-08-01
### Added
- [\#27] Signing requests can now be updated with the `enableInsecureSigning` flag

## [1.6.0] - 2017-06-13
### Added
- [\#25] Logging request and response bodies for API calls
```
class MyLogger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
        echo $level . ' : ' . (string) $message;
    }
}

Penneo\SDK\ApiConnector::setLogger(new MyLogger());
```

[comment]: # (Build Comparison Links)

[unreleased]: https://github.com/Penneo/sdk-php/compare/1.11.0...HEAD
[1.11.0]: https://github.com/Penneo/sdk-php/compare/1.10.0...1.11.0
[1.10.0]: https://github.com/Penneo/sdk-php/compare/1.9.0...1.10.0
[1.9.0]: https://github.com/Penneo/sdk-php/compare/1.8.0...1.9.0
[1.8.0]: https://github.com/Penneo/sdk-php/compare/1.7.0...1.8.0
[1.7.0]: https://github.com/Penneo/sdk-php/compare/1.6.0...1.7.0
[1.6.0]: https://github.com/Penneo/sdk-php/compare/1.5.0...1.6.0

[comment]: # (Issue Links)
[\#39]: https://github.com/Penneo/sdk-php/issues/39
[\#31]: https://github.com/Penneo/sdk-php/issues/31
[\#29]: https://github.com/Penneo/sdk-php/issues/29
[\#27]: https://github.com/Penneo/sdk-php/issues/27
[\#25]: https://github.com/Penneo/sdk-php/issues/25
