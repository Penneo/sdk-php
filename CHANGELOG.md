# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added

### Changed

### Fixed

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

[unreleased]: https://github.com/Penneo/sdk-php/compare/1.5.0...HEAD
<!-- [1.5.0]: https://github.com/Penneo/sdk-php/compare/1.4.0...1.5.0 -->

[comment]: # (Issue Links)
[\#25]: https://github.com/Penneo/sdk-php/issues/25
