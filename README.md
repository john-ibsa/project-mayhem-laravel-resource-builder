## Incendiary Blue Resource Builder

Generate fully built and tested API resources based on the Incendiary Blue internal standards.

## Overview

You can install the package via composer:

## Requirements

```bash
"laravel/framework": "^7.0",
"marcin-orlowski/laravel-api-response-builder": "^7.1",
"spatie/laravel-activitylog": "^3.14",
"spatie/laravel-permission": "^3.13"
```

## Installation

You can install the package via composer:

```bash
composer require incendiaryblue/laravel-resource-builder
```

## Usage

This package provides one simple artisan command that takes care of generating the following:

- Model
- Controller
- Requests for Index, Show, Store, Update, Destroy
- ApiCode Constants
- Feature Tests
- Database Migrations and Seeds

Run the following command:

```bash
composer require incendiaryblue/laravel-resource-builder --name "Resource Name" --code 40
```
