# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
- [\#22] Update guzzle

### Changed

### Fixed

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

[unreleased]: https://github.com/Penneo/sdk-php/compare/1.9.0...HEAD
[1.9.0]: https://github.com/Penneo/sdk-php/compare/1.8.0...1.9.0
[1.8.0]: https://github.com/Penneo/sdk-php/compare/1.7.0...1.8.0
[1.7.0]: https://github.com/Penneo/sdk-php/compare/1.6.0...1.7.0
[1.6.0]: https://github.com/Penneo/sdk-php/compare/1.5.0...1.6.0

[comment]: # (Issue Links)
[\#31]: https://github.com/Penneo/sdk-php/issues/31
[\#29]: https://github.com/Penneo/sdk-php/issues/29
[\#27]: https://github.com/Penneo/sdk-php/issues/27
[\#25]: https://github.com/Penneo/sdk-php/issues/25
