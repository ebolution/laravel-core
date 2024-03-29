# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.8] - 2024-01-24
### Changed
- Add float type in CastHelper

## [1.1.7] - 2024-01-19
### Added
- Require Logger Db in composer 

## [1.1.6] - 2024-01-19
### Changed
- AbstractArrayMapper - keep 'id' as the first array key 

## [1.1.5] - 2024-01-19
### Added
- Logger DB in ProcessTimer

## [1.1.4] - 2024-01-19
### Fixed
- Meta counters

## [1.1.3] - 2024-01-18
### Modified
- Meta from counts starting on one
- Make mappers global 

## [1.1.2] - 2024-01-17
### Fixed
- Listing between dates

## [1.1.1] - 2024-01-17
### Added
- ListingHelper class.

## [1.1.0] - 2024-01-16
### Added
- AbstractArrayMapper class and OutputMappings trait.

## [1.0.7] - 2023-12-12
### Changed
- Refactor. Classes moved from module to core component.

## [1.0.6] - 2023-11-06
### Added
- BuilderInterface contract and a implementation for Laravel framework

## [1.0.5] - 2023-10-10
### Changed
- FilterHelper accepts a Builder as base query (instead of a Model)

## [1.0.4] - 2023-10-05
### Added
- New helpers CastHelper, FilterHelper

## [1.0.3] - 2023-09-22
### Added
- New middleware IngestRouteParameters

## [1.0.2] - 2023-04-28
### Added
- LICENSE.md and .gitignore files

## [1.0.1] - 2023-04-28
### Added
Helper WithInputFiles which allows to store input files locally. 

## [1.0.0] - 2023-03-01
### Added
- This CHANGELOG file to hopefully serve as an evolving example of a
  standardized open source project CHANGELOG.
