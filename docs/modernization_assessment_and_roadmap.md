# TravianZ Comprehensive Modernization Assessment & Phased Engineering Roadmap

**Document Reference**: `docs/modernization_assessment_and_roadmap.md`  
**Status**: Definitive Architectural Baseline & Execution Roadmap  
**Author**: Technical Author & Systems Architect (TravianZ Modernization Initiative)  
**Date**: November 2026  
**Target Codebase**: TravianZ (PHP 8.3 / Apache / MariaDB Monolith)  
**Target Architecture**: Hexagonal / Clean Architecture, API-First, Event-Driven, Modern PHP 8.4+

---

## Table of Contents

1. [Executive Summary & Repository Health Scorecard](#1-executive-summary--repository-health-scorecard)
   - 1.1 Context and Executive Overview
   - 1.2 Quantitative Repository Profile & Composition Metrics
   - 1.3 Architectural & Quality Scorecard across Core Dimensions
   - 1.4 High-Level Risk Assessment & Technical Debt Analysis
2. [Comprehensive Codebase Audit & Architectural Evaluation](#2-comprehensive-codebase-audit--architectural-evaluation)
   - 2.1 Project Structure & Directory Layout
   - 2.2 Backend Technologies & PHP Version Compatibility
   - 2.3 Coding Standards, Global State & Design Patterns
   - 2.4 Core Game Loop & Automation Engine
   - 2.5 Database Schema, Storage & Indexing
   - 2.6 Data Access Layer & Concurrency
   - 2.7 Security Vulnerabilities Audit
   - 2.8 Frontend Assets, Templates & Client Scripts
   - 2.9 Infrastructure, Server Config & Runtime
   - 2.10 Testing, QA & CI/CD Pipelines
3. [Modernization Strategy & Technology Selection](#3-modernization-strategy--technology-selection)
   - 3.1 Target Architecture: Hexagonal / Clean Architecture & API-First
   - 3.2 Modern PHP Upgrade Path (PHP 8.3 / 8.4)
   - 3.3 Database Modernization & Concurrency Control
   - 3.4 Decoupled Game Engine & Event-Driven Queue
   - 3.5 Frontend Modernization & Real-Time Client
   - 3.6 Tooling, Linters, Static Analysis & CI/CD
4. [Phased Modernization Roadmap (Phases 0 through 5)](#4-phased-modernization-roadmap-phases-0-through-5)
   - 4.1 Phase 0: Tooling, Safety Net & Core Test Harness
   - 4.2 Phase 1: Infrastructure, Configuration & Container Foundations
   - 4.3 Phase 2: Database Layer Modernization & Security Hardening
   - 4.4 Phase 3: Domain Extraction, God Object Deconstruction & State Elimination
   - 4.5 Phase 4: Decoupled Game Loop & Asynchronous Event Engine
   - 4.6 Phase 5: Presentation Modernization, Routing, Twig & API-First Frontend
5. [Actionable Implementation Blueprint & Verification Matrix](#5-actionable-implementation-blueprint--verification-matrix)

---

## 1. Executive Summary & Repository Health Scorecard

### 1.1 Context and Executive Overview
TravianZ is an open-source browser-based massive multiplayer online real-time strategy (MMORTS) game engine recreating the gameplay, mechanics, and UI of Travian T3.6. Originally developed in the mid-2000s under the LAMP (Linux, Apache, MySQL, PHP 5) paradigm, the codebase has undergone incremental community patches over more than fifteen years.

While the project successfully maintains the complex, nostalgic game mechanics of classic Travian—such as non-linear battle formulas, multi-tribe combat calculations, building upgrade queues, oasis troop regeneration, and World Wonder mechanics—its underlying technical architecture remains fundamentally coupled to legacy paradigms:
- The web server document root is identical to the repository root, exposing all source files, configuration templates, and administrative scripts.
- The runtime operates on `php:8.3-apache`, but compatibility is achieved purely through aggressive error suppression (`error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE & ~E_WARNING`) and disabled database strict modes (`--sql_mode=""`).
- Massive God Objects (`MYSQLi_DB` spanning 15 traits and `Automation` spanning 14 traits) dictate application flow, heavily polluting global memory with procedural state (`$database`, `$session`, `$building`, `$village`, `$bid1..45`, `$u1..100`).
- Over 699 raw `mysqli_query` calls interpolate unescaped variables directly into SQL statements, lacking prepared statements, database transactions, and foreign key constraints across 60+ tables.
- Core player actions lack CSRF tokens, session IDs are never regenerated upon authentication, and admin templates reflect unescaped user parameters.
- The client experience relies on XHTML 1.0, 1990s HTML `<map>`/`<area>` image maps with hardcoded pixel coordinates, a 9,713-line fixed 980px stylesheet, competing MooTools 1.2.4/1.2.5 libraries, and an unthrottled 30-second client-side polling ping that triggers backend automation routines.

This assessment provides an exhaustive audit of the TravianZ codebase based on empirical static analysis and delivers a production-grade, 6-phase engineering roadmap to modernize the project into a high-performance, secure, and maintainable PHP 8.4+ Clean Architecture application.

---

### 1.2 Quantitative Repository Profile & Composition Metrics

An automated inventory across the Git repository reveals the following structural volume and asset distribution:

| Metric Category | Specific Item | Quantitative Count / Volume | Architectural Implication |
| :--- | :--- | :--- | :--- |
| **Git Tracked Files** | Total Tracked Assets | **4,288 files** | Large repository footprint mixing code, binary media, and configs |
| **Backend Code** | PHP Scripts (`.php`) | **406 files** | Widespread procedural script entrypoints in web root |
| **Template Files** | Template Partials (`.tpl`) | **716 files** | Disguised raw PHP execution files without a templating engine |
| **Total Source Lines** | Combined PHP + TPL Code | **148,067 lines** | Substantial procedural volume requiring systematic modernization |
| **Media Assets** | Binary Images (`.gif`, `.jpg`, `.png`) | **3,051 files** | 1,699 GIFs, 808 JPGs, 544 PNGs committed directly in git |
| **Zipped Graphic Packs** | Tracked Archive Blobs | **2 archives (17.05 MB)** | `travian_default.zip` (6.18 MB) and `travian_t4.zip` (10.87 MB) |
| **Stylesheets & JS** | CSS & Client Script Files | **31 CSS, 13 JS files** | Fixed 980px stylesheets; 5 duplicate MooTools bundles |
| **Web Server Configs** | Distributed `.htaccess` | **33 files** | Outdated Apache 2.2 access rules scattered in subfolders |
| **Database Schema** | Tables in `var/db/struct.sql` | **60+ tables (2,513 lines)** | 0 Foreign Keys, denormalized 80-column `fdata` (lines 893-982), 94-column `units` (up to `u90`), 100-column `users` (112 definition lines) |
| **Database Queries** | Raw `mysqli_query` calls | **699 occurrences** | Widespread SQL string concatenation; 0 prepared statement usage |
| **Dependencies** | Composer & NPM Manifests | **0 manifests** | Total absence of `composer.json` and `package.json` |
| **Test Coverage** | Unit / Regression Tests | **9 executable test scripts** | 8 standalone PHP scripts + 1 shell script; 0% automated coverage on combat, building, and economy rules |

---

### 1.3 Architectural & Quality Scorecard across Core Dimensions

The repository was audited against modern software engineering standards across ten core dimensions. Each dimension is scored from **F (Critical Failure / Severe Debt)** to **A (Modern Industry Standard)**:

```
+---------------------------------------------------------------------------------------------------------+
| DIMENSION                                  | GRADE | PRIMARY DEFICIENCY / RISK VECTOR                   |
+---------------------------------------------------------------------------------------------------------+
| 1. Project Structure & Layout              |   F   | No public/ DocumentRoot; 406 root entrypoints      |
| 2. Backend Technologies & PHP Baseline     |   D-  | Masked PHP 8.3 runtime; error suppression flags    |
| 3. Coding Standards & State Management     |   F   | God Objects; 29 traits; pervasive global variables |
| 4. Core Game Loop & Automation Engine      |   D-  | Web-request fallback; 25 sequential file locks     |
| 5. Database Schema & Storage Design        |   F   | 0 Foreign Keys; utf8mb3/mb4 mismatch; wide tables  |
| 6. Data Access Layer & Concurrency         |   F   | 699 raw queries; no transactions; dead query_new   |
| 7. Security & OWASP Hardening              |   F   | SQLi vectors; 0 CSRF tokens; session fixation      |
| 8. Frontend Assets & Client Scripting      |   F   | XHTML 1.0; image maps; 30s polling; MooTools/Flash |
| 9. Infrastructure & Server Config          |   D-  | 33 Apache 2.2 .htaccess files; no .env in PHP      |
| 10. Testing, Quality Assurance & CI/CD     |   F   | 0% core mechanics coverage; no PHPStan/PHPUnit     |
+---------------------------------------------------------------------------------------------------------+
OVERALL SYSTEM HEALTH SCORE: F (CRITICAL REFACTORING REQUIRED)
```

---

### 1.4 High-Level Risk Assessment & Technical Debt Analysis

1. **Security Vulnerability Exposure (Critical Severity)**:
   - Dynamic SQL identifier and column injection vectors in alliance management (`aExist`), trade routes (`editTradeRoute`), and activation (`getActivateField`).
   - Total omission of Anti-CSRF protection across core player actions (`a2b.php` attack orders, building upgrades, alliance disbanding, gold shop purchases).
   - Zero session ID regeneration (`session_regenerate_id`) upon login, enabling trivial session hijacking.
   - Commented-out security middleware (`Security::instance()` returns `NULL`).
2. **PHP 9.0 Compatibility Cliff (High Severity)**:
   - Widespread dynamic property assignments in core classes (`Session`, `Building`, `Village`, `Battle`) without `#[\AllowDynamicProperties]`. Deprecated in PHP 8.2, dynamic properties trigger fatal runtime exceptions in PHP 9.0.
   - Deprecated PHP 4 `var` syntax and implicitly nullable parameter types (`$param = null` without `?Type`) trigger deprecation cascades when error suppression is lifted.
3. **Database Concurrency & Data Corruption Risk (Critical Severity)**:
   - Zero ACID multi-table transactions across combat resolutions, building completion, and marketplace trading. Process interruptions leave defender troops dead, attacker troops duplicated, and resource vaults negative.
   - Severe N+1 query patterns: `AutomationVillageUpkeep::updateStore()` executes a full table scan on `fdata` followed by 20,001 individual sequential `UPDATE` queries on every tick.
   - Runtime DDL statements (`CREATE TABLE IF NOT EXISTS` in `GoldShop`, `MultiAccount`, `PushProtection`) trigger MySQL Metadata Locks (MDL), serializing concurrent incoming web traffic and causing deadlock spikes.
4. **Game Loop Bottleneck & Denial of Service (High Severity)**:
   - Web-request execution fallback in `Village.php:478-506`: When cron is delayed or unconfigured, end-user HTTP requests stall while synchronously executing background combat math and building completions across all villages on the server.
   - Tick execution locks and unlocks 25 separate filesystem files in `GameEngine/Prevention/` sequentially.
5. **Architectural Rigidity & Maintenance Paralysis (High Severity)**:
   - Zero dependency management (no Composer), brittle 5-level relative path autoloader throwing unhandled exceptions, and complete lack of automated unit/integration tests for core business formulas.
   - Any refactoring carries extreme regression risk until combat formulas and business rules are codified into automated regression test harnesses.

---

## 2. Comprehensive Codebase Audit & Architectural Evaluation

### 2.1 Project Structure & Directory Layout

#### DocumentRoot Exposure and Entrypoint Coupling
The primary web server configuration directly exposes the repository root as the Apache DocumentRoot (`WORKDIR /var/www/html` in `Dockerfile:32`, `docker-compose.yml:12`). There is no dedicated public directory (e.g. `public/` or `web/`).

As a result, **406 individual `.php` scripts** and **716 `.tpl` files** reside directly in the web-accessible tree. Each game feature represents an isolated script entrypoint rather than a routed controller action:
- Village & Resource management: `dorf1.php`, `dorf2.php`, `dorf3.php`
- Building construction & management: `build.php`, `build_croppers.php`
- Troop movements & military rally point: `a2b.php`, `a2b2.php`
- Alliance management: `allianz.php`
- Reports and messaging: `berichte.php`, `nachrichten.php`
- Account authentication & lifecycle: `login.php`, `logout.php`, `anmelden.php`, `activate.php`, `password.php`
- World map navigation: `karte.php`, `karte2.php`
- Battle simulation & statistics: `warsim.php`, `statistiken.php`
- Background execution: `cron.php`
- Dynamic endpoints: `ajax.php`

```
CURRENT VULNERABLE MONOLITHIC DIRECTORY LAYOUT:
c:\Users\batah\Documents\GitHub\TravianZ\
├── .env.example             <-- Exposed if web server allows dotfiles
├── Dockerfile               <-- Directly accessible via HTTP GET /Dockerfile
├── docker-compose.yml       <-- Directly accessible via HTTP GET /docker-compose.yml
├── autoloader.php           <-- Custom brittle autoloader
├── dorf1.php                <-- Monolithic entrypoint (presentation + DB + session)
├── dorf2.php                <-- Monolithic entrypoint
├── build.php                <-- Monolithic entrypoint
├── a2b.php                  <-- Monolithic entrypoint (attacks/raids)
├── cron.php                 <-- Standalone cron tick runner
├── GameEngine/              <-- Core engine classes, traits, and configs directly exposed
│   ├── config.php           <-- Contains plaintext database credentials
│   ├── Database.php         <-- God object instantiation with side effects
│   ├── Session.php          <-- Global session handler
│   ├── Automation.php       <-- 25 file-locked sequential tasks
│   └── Prevention/          <-- 25 file locks (*.txt) directly in web tree
├── Templates/               <-- 652 raw PHP files disguised as .tpl
├── Admin/                   <-- Administrative scripts directly in web root
├── var/                     <-- SQLite/MySQL schemas and logs (var/log/access.log)
└── gpack/                   <-- Static assets mixed with 17 MB binary zip archives
```

#### Absence of Front Controller & Routing Engine
The application possesses **no Front Controller pattern** (`public/index.php`), **no URL routing engine** (such as FastRoute or Symfony Routing), and **no HTTP Middleware pipeline** (PSR-15). 

Every incoming request initializes the application independently by manually including headers, session bootstrap, and database wrappers:
- `dorf1.php:27` explicitly executes `include_once("GameEngine/Village.php");` (which in turn includes `GameEngine/Building.php` at `Village.php:470`; lines 14–25 in `dorf1.php` are license and header comments).
- Inclusions immediately execute side-effect initializations in global scope.
- Global scope variables, database connections (`$database`), and user sessions (`$session`) are implicitly leaked into included template files (`dorf1.php:80-118`).
- Without a central middleware pipeline, foundational cross-cutting concerns—such as security headers (CSP, HSTS, X-Content-Type-Options), global rate limiting, CSRF verification, and session regeneration—cannot be implemented cleanly and must be duplicated across hundreds of script files.

#### Subdirectory Inventory & Divergent Implementations
1. `GameEngine/`: Contains 35 procedural/OOP classes and 8 subdirectories (`Database/`, `Automation/`, `Data/`, `Admin/`, `Lang/`). God Objects dominate this namespace.
2. `src/`: A nascent, incomplete attempt at modern PSR-4 structure containing only 7 classes:
   - `src/Database/IDbConnection.php`
   - `src/Entity/User.php`
   - `src/Utils/AccessLogger.php`
   - `src/Utils/DateTime.php`
   - `src/Utils/DebugErrorLogger.php`
   - `src/Utils/IpResolver.php`
   - `src/Utils/Math.php`
   This directory represents less than 2% of the repository's functional logic, creating an awkward architectural dichotomy between `src/` and `GameEngine/`.
3. `Admin/` vs `GameEngine/Admin/`: The repository maintains two parallel, competing administrative implementations:
   - `Admin/admin.php` (43 KB) contains an expansive control panel implementation with ad-hoc CSRF tokens.
   - `GameEngine/Admin/admin.php` (5 KB) provides an alternate administrative routine with divergent permission checks and database methods.
4. `Multi-Instance Server/`: Contains an unmaintained, duplicated code snapshot with separate copies of `anmelden.php`, `index.php`, `login.php`, and templates.
5. `var/`: Stores database schema definitions (`var/db/struct.sql`), raw world data generators, and access logs (`var/log/access.log`) within the web root.

---

### 2.2 Backend Technologies & PHP Version Compatibility

#### Runtime Environment vs Legacy Codebase
- The execution container is defined as `FROM php:8.3-apache` in `Dockerfile:1`.
- The database service is defined as `mariadb:latest` in `docker-compose.yml:21`.
- Although running on a PHP 8.3 container, the underlying codebase was authored for PHP 5.x and legacy PHP 7.x. Full compatibility is an illusion maintained via two aggressive suppression mechanisms:
  1. **PHP Error Reporting Suppression (`docker/php/zz-travianz.ini:9`)**:
     ```ini
     error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE & ~E_WARNING
     ```
     By masking `E_DEPRECATED`, `E_STRICT`, `E_NOTICE`, and `E_WARNING`, the application hides thousands of runtime violations that would otherwise flood server logs and break page rendering.
  2. **MariaDB SQL Mode Suppression (`docker-compose.yml:35`)**:
     ```yaml
     command: --sql_mode=""
     ```
     MariaDB's strict modes (`STRICT_TRANS_TABLES`, `ONLY_FULL_GROUP_BY`, `NO_ZERO_IN_DATE`, `NO_ZERO_DATE`) are completely disabled. Legacy SQL queries rely on implicit column defaults, zero-dates (`0000-00-00 00:00:00`), and non-aggregated SELECT clauses that fail under standard RDBMS configurations.

#### Critical PHP 8.x Compatibility Defects

##### 1. Dynamic Properties vs Legacy Untyped Declarations (Deprecated in PHP 8.2, Fatal Error in PHP 9.0)
In PHP 8.2+, assigning undeclared dynamic properties on classes without the `#[\AllowDynamicProperties]` attribute triggers `E_DEPRECATED`. In PHP 9.0, this becomes a fatal `Error`.
A critical distinction must be drawn between legacy declared properties and genuine runtime dynamic property assignments across the codebase:
- **True Runtime Dynamic Property Assignments**: Core classes frequently assign undeclared instance properties at runtime without `#[\AllowDynamicProperties]`. For example, `GameEngine/Session.php:133` executes `$this->time = time();` where `$time` is never declared on the class. Similarly, `Automation.php` and various procedural handlers inject ad-hoc runtime flags directly onto instantiated objects.
- **Legacy Declared Properties Lacking Modern Types**: In `GameEngine/Village.php:37-65`, 35 properties (such as `$resarray`, `$infoarray`, `$coor`) are explicitly declared, but they lack type definitions and modern visibility modifiers. In `GameEngine/Session.php:89-105`, properties such as `$tribe`, `$plus`, and `$goldclub` are declared using legacy PHP 4 `var` syntax (note: the T4 economy feature `$silver` does not exist in the TravianZ T3.6 codebase). While these declared properties do not trigger PHP 8.2 dynamic property deprecations, they violate modern typed OOP standards.
- **Battle Calculation Scope**: In `GameEngine/Battle.php`, complex combat calculations store and manipulate battle casualty metrics and resource bounties using local associative array structures (`$result['casualties']`, `$result['bounty']`) returned from calculation methods, rather than dynamically attaching them to class instances. However, these methods rely on unstructured array structures that lack formal DTO contracts.

##### 2. Deprecated PHP 4 Property Declarations (`var $prop`)
PHP 4 style `var` declarations persist in foundational classes:
- `GameEngine/Session.php:89-129`: `var $logged_in`, `var $inAdmin`, `var $bonus`, `var $sit1`, `var $tribe`, `var $plus`, `var $goldclub`.
- `GameEngine/Admin/database.php:58`: `var $connection`.
These declarations lack visibility modifiers (`public`, `protected`, `private`) and modern type declarations (`int`, `string`, `bool`), forcing developers to rely on PHP error suppression masks to avoid strict standards notices.

##### 3. Strict Parameter Types & Null Handling (PHP 8.1+)
Passing `null` to built-in string/array functions (`strlen()`, `trim()`, `substr()`, `explode()`) triggers `E_DEPRECATED` in PHP 8.1+.
While defensive patches were added recently (e.g. `CHANGELOG.md:42-62`, `ajax.php:51`), thousands of un-guarded calls remain in `GameEngine/Automation/AutomationBattleResolution.php` and `GameEngine/Database/*.php`.

##### 4. Implicitly Nullable Parameter Types (Deprecated in PHP 8.4)
Signatures across `DatabaseConnectionCore.php`, `Automation.php`, and `Building.php` declare optional parameters with default `null` without explicit nullable type syntax:
```php
// Deprecated in PHP 8.4:
function clearQueryCache($prefix = null)
// Required in PHP 8.4+:
function clearQueryCache(?string $prefix = null)
```

##### 5. MySQLi Default Error Mode (`MYSQLI_REPORT_STRICT`)
In PHP 8.1+, MySQLi defaults to throwing `mysqli_sql_exception` on query errors instead of returning `false`. This change caused fatal crashes during deadlocks or missing records, prompting ad-hoc retry loops:
- `GameEngine/Database/DatabaseConnectionCore.php:298-315`: A custom `while(true)` loop catching `mysqli_sql_exception` error codes 1213 (deadlock), 1205 (lock wait timeout), 2006/2013 (server gone away).
- `GameEngine/Database/DatabaseUserQueries.php:45-53`: Guard added to catch duplicate entry crashes on Natars registration.

##### 6. Flawed Custom Autoloader (`autoloader.php:23-54`)
```php
function autoloadClass($class) {
    $clazz = str_replace(['App\\', '\\'], ['', '/'], $class);
    $class_found = false;
    for ($i = 0; $i < 5; $i++) {
        $autoprefix = str_repeat('../', $i);
        $path1 = $autoprefix . 'src/' . $clazz . '.php';
        $path2 = $autoprefix . 'GameEngine/' . $clazz . '.php';
        if (file_exists($path1)) { include_once $path1; $class_found = true; break; }
        if (file_exists($path2)) { include_once $path2; $class_found = true; break; }
    }
    if (!$class_found) {
        throw new Exception('Unable to find class ' . $clazz . '.');
    }
}
spl_autoload_register('autoloadClass');
```
**Fatal Architectural Flaw**: Throwing an uncaught `Exception` on line 52 violates the PHP autoloading standard (`spl_autoload_register`). An autoloader must return `void` or `false` to allow chained autoloaders to attempt resolution. Any invocation of `class_exists($className, true)` on a class outside `src/` or `GameEngine/` immediately crashes the entire PHP execution.
Furthermore, traversing `str_repeat('../', $i)` based on `getcwd()` breaks when scripts are invoked from CLI subdirectories (e.g. `cron.php:74` required an explicit `@chdir(__DIR__)` hotfix).

##### 7. Obsolete PHP 5 Security Constructs
`Security/Security.class.php:74-89` checks for `get_magic_quotes_runtime()`, `get_magic_quotes_gpc()`, and `register_globals`—features removed from PHP over a decade ago. While included in `index.php:44`, `Security::instance()` is commented out, rendering the class completely inert.

---

### 2.3 Coding Standards, Global State & Design Patterns

#### Procedural vs OOP Disharmony
The codebase exhibits a profound split personality:
- Classes are instantiated primarily as stateful namespaces rather than cohesive object models.
- Controllers do not exist; scripts mix HTTP input processing (`$_GET`, `$_POST`), direct SQL query construction, business logic calculations, session mutations, and XHTML markup rendering within a single file (e.g., `dorf1.php:30-46`, `Units.php:1-200`).
- Business operations issue raw `header("Location: ...")` redirects followed by `exit;` deep within service classes.

#### God Objects & Trait Sprawl
Recent refactoring phases ("Phase S1" and "Phase S2") attempted to decompose monolithic classes by splitting them into PHP traits:
- `MYSQLi_DB` in `GameEngine/Database.php` uses **15 traits**:
  `DatabaseConnectionCore`, `DatabaseUserQueries`, `DatabaseVillageQueries`, `DatabaseTroopQueries`, `DatabaseAllianceQueries`, `DatabaseBuildingQueries`, `DatabaseMarketQueries`, `DatabaseMovementQueries`, `DatabaseReportQueries`, `DatabaseMessageQueries`, `DatabaseHeroQueries`, `DatabaseQuestQueries`, `DatabaseAdminQueries`, `DatabaseChatQueries`, `DatabasePaypalQueries`.
- `Automation` in `GameEngine/Automation.php` uses **14 traits**:
  `AutomationBattleResolution`, `AutomationVillageUpkeep`, `AutomationBuildQueue`, `AutomationMarketQueue`, `AutomationDemolitionQueue`, `AutomationResearchQueue`, `AutomationTrainingQueue`, `AutomationStarvation`, `AutomationLoyalty`, `AutomationSettlersQueue`, `AutomationNatars`, `AutomationArtifacts`, `AutomationHero`, `AutomationAlliance`.

```
ILLUSTRATION: THE GOD OBJECT / TRAIT SPRAWL PATTERN

             +-------------------------------------------------+
             |           MYSQLi_DB (God Object)                |
             |       (15 Traits, ~12,000 Combined Lines)       |
             +-------------------------------------------------+
               │         │         │         │         │
               ▼         ▼         ▼         ▼         ▼
           [Connect]  [User]   [Village]  [Troop]  [Alliance]
           [Building] [Market] [Movement] [Report] [Message]
           [Hero]     [Quest]  [Admin]    [Chat]   [PayPal]
               │         │         │         │         │
               └─────────┴────┬────┴─────────┴─────────┘
                              │
               All 15 traits share private state,
               untyped $this->dblink, global caches,
               and unchecked method dependencies.
```

**Architectural Analysis**: Trait decomposition without interface segregation or domain decoupling is merely *file partitioning*, not modular architecture. All 15 database traits share private state, untyped `$this->dblink`, global caches, and unvalidated cross-trait method invocations. The resulting God Object remains tightly coupled, untestable in isolation, and impossible to mock.

#### Side-Effect Instantiation on Script Inclusion
Inclusion of core engine files immediately executes instantiation and global state injection:
- `GameEngine/Database.php:514-525`:
  ```php
  $database = new MYSQLi_DB(SQL_SERVER, SQL_USER, SQL_PASS, SQL_DB, ...);
  $link = $database->return_link();
  $GLOBALS['db'] = $database;
  $GLOBALS['link'] = $database->return_link();
  ```
- `GameEngine/Session.php:764-770`:
  ```php
  $session = new Session;
  $form = new Form;
  if (!empty($_SESSION['id_user'])) {
      $message = new Message;
      $user = new User((int)$_SESSION['id_user'], $database);
  }
  ```
- `GameEngine/Village.php:469-470`:
  ```php
  $village = new Village;
  $building = new Building;
  ```
- `GameEngine/Automation.php:199`:
  ```php
  $automation = new Automation;
  ```
This pattern makes classes impossible to unit test in isolation, prevents dependency injection, and causes severe scope leakage.

#### Global Array Data Stores
Core game configuration, unit statistics, and building progressions are defined as raw procedural global arrays in `GameEngine/Data/`:
- `buidata.php:24-60`: 45 global arrays (`$bid1` through `$bid45`) defining resource costs, build times, population, and culture points for levels 1–20.
- `unitdata.php:23-60`: `$unitsbytype` and 100 global arrays (`$u1` through `$u100`) defining unit attack, defense, speed, carry capacity, and training costs.
- `resdata.php`: Global production lookup matrices.
- `hero_full.php`: 201 KB of raw global arrays defining hero items and attributes.

Domain logic accesses these configurations via `global $bid1, $bid2, ...` or `$GLOBALS["bid".$tbgid]` (`AutomationBattleResolution.php:82`), completely bypassing encapsulation, type checking, and immutability.

---

### 2.4 Core Game Loop & Automation Engine

#### Dual Invocation Pipeline Architecture
The engine relies on a bifurcated execution model for processing background game ticks:
1. **Standalone CLI Cron (`cron.php`)**:
   - Designed for CLI invocation: executes a tick every 60 seconds (`CRON_TICK_SECONDS = 60`) for up to 5 minutes (`CRON_LOOP_SECONDS = 300`) to conform to shared hosting execution limits (`cron.php:13-25`).
   - Uses an atomic filesystem lock: `GameEngine/Prevention/cron_running.lock` (`cron.php:148-161`).
   - Updates a heartbeat marker file `GameEngine/Prevention/cron_active.txt` with the current Unix timestamp on every completed tick.
   - Performs reflection-based memory cache clearing between ticks: `cron_reset_db_caches()` (`cron.php:198-227`) uses `ReflectionClass('MYSQLi_DB')` to wipe static array caches that would otherwise cause state drift across ticks.
2. **Web-Request Fallback (`GameEngine/Village.php:478-506`)**:
   - Whenever any player loads a page requiring `Village.php` (e.g. `dorf1.php`, `dorf2.php`, `build.php`), it inspects `cron_active.txt`.
   - If the timestamp is older than 90 seconds (or missing), it acquires `fopen(AUTOMATION_LOCK_FILE_NAME, 'c')` with `flock(LOCK_EX | LOCK_NB)` and runs `include_once("Automation.php")`.
   - **Severe Latency Impact**: If cron is delayed or unconfigured, end-user HTTP requests stall while executing background combat calculations, building completions, and starvation updates for all villages on the server.

```
CURRENT TICK EXECUTION DUAL PIPELINE:

      [CLI Cron Runner]                          [Player HTTP Request]
         (cron.php)                                   (dorf1.php)
              │                                            │
              ▼                                            ▼
   Acquire cron_running.lock                       Includes Village.php
              │                                            │
              ▼                                            ▼
    Execute Automation Tick                       Check cron_active.txt
              │                                            │
              ▼                                            ├──> If < 90s: Render Page
   Update cron_active.txt                                  │
              │                                            └──> If > 90s: FALLBACK TRIGGER
              ▼                                                    │
     Wait 60s / Loop                                               ▼
                                                          Acquire AUTOMATION_LOCK
                                                                   │
                                                                   ▼
                                                          Synchronously Execute
                                                          Full Automation Tick!
                                                          (Page stalls for seconds)
```

#### The 25 Sequential File Lock Bottleneck
Within `GameEngine/Automation.php:150-168`, tick execution executes 25 sequential tasks locked by individual files on disk:
```php
$methodsArrays = [
    "culturePoints", "updateHero", "clearDeleting", "buildComplete",
    "demolitionComplete", "marketComplete", "researchComplete", "trainingComplete",
    "healingComplete", "starvation", "celebrationComplete", "festivalComplete",
    "sendUnitsComplete", "loyaltyRegeneration", "sendreinfunitsComplete",
    "returnunitsComplete", "sendSettlersComplete", "spawnNatars", "spawnWWVillages",
    "spawnWWBuildingPlans", "activateArtifacts", "heroAdventureComplete",
    "cleanupOldData", "recordPlayerStatistics", "buildNatarsWonder"
];
foreach($methodsArrays as $method){
    $file = fopen($autoprefix."GameEngine/Prevention/".$method.".txt", "w");
    if(flock($file, LOCK_EX)) {
        call_user_func(array($this, $method));
        flock($file, LOCK_UN);
    }
    fclose($file);
}
```
**Performance Defect**: Every tick opens, acquires an exclusive lock, releases, and closes at least 25 individual files in `GameEngine/Prevention/`. On distributed filesystems or container bind-mounts, this produces extreme I/O overhead and lock contention.

#### Monolithic Combat Resolution Engine
Combat processing is concentrated in `AutomationBattleResolution.php` (3,357 lines in a single trait) and `Battle.php` (2,064 lines):
- Processes `sendunitsComplete` (entrypoint at line 2616): iterates over movements in `vdata` and `enforce`, joins attacker and defender village records, evaluates wall levels, palace/residence defensive bonuses, hero equipment modifiers, and brewery bonuses.
- Evaluates non-linear battle models in `Battle::calculateBattle()` (`Battle.php:30-32`):
  $$\sigma(x) = \frac{x > 1 ? 2 - x^{-1.5} : x^{1.5}}{2}$$
- Calculates casualties, catapult building reductions (`calculateCatapultsDamage()`), ram wall reductions, hero XP gain, resource bounties (`computeBounty()`), and chieftain loyalty reductions.
- **Synchronous Execution Spike**: If multiple attack waves land simultaneously (e.g. during an alliance coordinated strike with hundreds of incoming attacks), execution time spikes into dozens of seconds, blocking background threads and exhausting database connections.

---

### 2.5 Database Schema, Storage & Indexing

#### Schema Sources and Foreign Key Absence
The foundational database schema is defined in `var/db/struct.sql` (2,513 lines across 60+ tables), with static data loaded from `datagen-world-data.sql` and `datagen-oasis-troops-regen.sql`.
- **Storage Engine**: Tables declare `ENGINE=InnoDB` (`var/db/struct.sql:43, 91, 118, 530, 2192`).
- **Zero Foreign Keys**: An exhaustive search across `var/db/struct.sql` and all SQL files for `FOREIGN KEY` or `REFERENCES` yielded **0 occurrences**.
- **Orphan Record Accumulation**: Referential integrity is enforced solely through procedural application code. When user deletion occurs in `GameEngine/Admin/Mods/delUser.php:78-87`, cascading deletions require 10 separate ad-hoc `DELETE` statements. If a statement fails or is missed, orphaned records remain indefinitely in `vdata`, `fdata`, `units`, `mdata`, or `ndata`.

#### Character Set and Collation Inconsistency
- Legacy tables specify `DEFAULT CHARSET=utf8` (which in MySQL/MariaDB denotes `utf8mb3`, restricted to 3 bytes per character and incapable of storing 4-byte characters such as emojis or supplementary Unicode planes):
  `var/db/struct.sql:43` (`a2b`), `var/db/struct.sql:2192` (`users`), `var/db/struct.sql:2256` (`vdata`).
- Newer tables specify `DEFAULT CHARSET=utf8mb4`:
  `var/db/struct.sql:605` (`chat_global`), `var/db/struct.sql:643` (`chat_global_polls`), `var/db/struct.sql:2337` (`croppers`).
- In `GameEngine/Database.php:506`, the database connection executes a forced `mysqli_query($this->dblink, "SET NAMES 'utf8mb4'");` override to prevent emoji truncation in global chat:
  ```php
  // GameEngine/Database.php:500-506
  // FIX critic - linia asta rula "SET NAMES 'UTF8'" (3 octeti/caracter) IMEDIAT dupa connect(),
  // suprascriind silentios mysqli_set_charset('utf8mb4') facut in connect()
  mysqli_query($this->dblink,"SET NAMES 'utf8mb4'");
  ```
  This mismatch between table collation (`utf8mb3_general_ci`) and connection collation (`utf8mb4`) creates hidden character conversion overhead and triggers latent `Illegal mix of collations` errors during cross-table JOIN queries.

#### Table Design Anti-Patterns (Extreme Denormalization)

##### 1. The `fdata` Field Table (`var/db/struct.sql:893-982`)
Stores 40 village building slots across **80 individual flat columns**: `f1` through `f40` (building levels) and `f1t` through `f40t` (building types). (Lines 984+ in `struct.sql` define forum tables).
To determine which slot contains a specific building (e.g. Barracks `tid=19`), PHP code must iterate over all 40 columns in a loop:
```php
// GameEngine/Building.php:1327-1335
foreach ($resourcearray as $key => $value) {
    if ($value == $tid && strpos($key, 't') !== false) {
        $keyholder[] = (int) preg_replace('/[^0-9]/', '', $key);
    }
}
```

##### 2. The `units` Table (`var/db/struct.sql:1970-2066`)
Contains **94 flat columns** (`vref`, `u1` through `u90`, `u99`, `hero`), representing an extreme level of table denormalization.
Each tribe only utilizes 10 unit columns (Romans: `u1`-`u10`, Teutons: `u11`-`u20`, Gauls: `u21`-`u30`, Nature: `u31`-`u40`, Natars: `u41`-`u50`, with unused slots extending through `u90`). Over 85% of column storage per village row is permanently wasted with zero or null values.
Adding a new unit or tribe requires modifying table definitions across `units` (94 columns), `tdata` (50 columns), `abdata` (100 columns: `a1`-`a50`, `b1`-`b50`), and `hospital` (90 columns: `h1`-`h90`).

##### 3. The `users` Table (`var/db/struct.sql:2080-2192`)
Stores **100 flat columns** across 112 table definition lines within a single row, combining core authentication credentials, sitting permissions (`sit1`, `sit2`, `sit1_perm`, `sit2_perm`), bonus timers (`b1`-`b4`), vacation timestamps, and 19 separate friend waitlist columns (`friend1wait` to `friend19wait`).

#### Index Deficiencies & Full Table Scans
- `market` (`var/db/struct.sql:1465-1478`): Only indexed on `PRIMARY KEY (id)` and `KEY vref-accept-merchant (vref, accept, merchant) USING BTREE`.
  When querying marketplace offers in `DatabaseMarketQueries.php:273`:
  ```sql
  SELECT * FROM market WHERE vref != $vid AND alliance = $alliance OR vref != $vid AND alliance = 0 AND accept = 0;
  ```
  Because `vref` is the leading index column and evaluated with inequality `!=`, MySQL cannot use this index effectively, triggering full table scans of active offers.
- `attacks` (`var/db/struct.sql:429-458`): Indexed on `vref` and `attack_type`, but lacks indexes on destination village or execution timestamp.
- Logging tables (`admin_log`, `build_log`, `illegal_log`, `login_log`, `market_log`, `tech_log`): Only possess `PRIMARY KEY (id)` without indexes on `user`/`wid`, `ip`, or `time`, degrading administrative log searches into disk-bound table scans.

---

### 2.6 Data Access Layer & Concurrency

#### Proliferation of Raw Queries & Dead Abstractions
- **Raw Query Proliferation**:
  - `mysqli_query`: **699 direct occurrences** scattered across procedural scripts, templates, and administrative modules.
  - `$database->query(...)`: **259 occurrences** taking concatenated SQL strings.
- **Dead Prepared Statement Abstraction**:
  - `GameEngine/Database/DatabaseConnectionCore.php:125-241` contains an experimental prepared statement method `query_new($statement, ...$params)`.
  - An audit across the entire repository revealed that while exactly 1 prototype caller exists in `src/Entity/User.php:96` (`User::exists`), it is an uncalled prototype method in modern execution. Across all active game paths, `query_new` is dead code; 100% of production database queries rely on string concatenation and raw `mysqli_query`.

#### Flawed Escaping and Sanitization Routines
String escaping is centralized in `DatabaseConnectionCore.php:360-367`:
```php
function escape($value) {
    if (is_string($value)) {
        $value = stripslashes( $value );
        return mysqli_real_escape_string($this->dblink, $value);
    } else {
        return $value;
    }
}
```
1. **Input Corruption via `stripslashes`**: If user input legitimately contains backslashes (e.g. passwords, LaTeX formulas, regexes, JSON payloads), `stripslashes()` silently mutates the user payload.
2. **Numeric Parameter Vulnerability**: If `$value` is not explicitly cast to `(int)` before interpolation into numeric SQL positions (e.g. `WHERE id = $id`), `mysqli_real_escape_string` provides **zero protection** against boolean or union injection payloads (e.g., `1 OR 1=1`).
3. **Mixed Concerns in `RemoveXSS` (`DatabaseConnectionCore.php:319-323`)**:
   ```php
   function RemoveXSS($val) {
       list($val) = $this->escape_input($val);
       return htmlspecialchars($val, ENT_QUOTES);
   }
   ```
   This routine conflates SQL escaping (`mysqli_real_escape_string`) and HTML output encoding (`htmlspecialchars`) within the storage layer, causing double-escaping (`&amp;quot;`) when data is retrieved for non-HTML contexts.

#### Severe N+1 Query Patterns
1. **Automation Village Upkeep (`AutomationVillageUpkeep.php:424-486`)**:
   `updateStore()` performs a full table scan on `fdata`:
   ```sql
   SELECT * FROM fdata;
   ```
   It then iterates over every row in PHP and executes a separate `UPDATE` statement for each village:
   ```sql
   UPDATE vdata SET maxstore = ..., maxcrop = ... WHERE wref = $row['vref'];
   ```
   On a server with 20,000 villages, this single method issues **20,001 separate sequential queries on every tick**.
2. **Starvation Processing (`AutomationStarvation.php:59-65`)**:
   `starvationUpdateAllVillages()` retrieves all villages via `$database->getProfileVillages(0, 7)`.
   In a `foreach` loop, it invokes `$database->addStarvationData($starv['wref'])` (`DatabaseVillageQueries.php:2398-2420`).
   For every village, `addStarvationData` executes `getVillage()`, `getCropProdstarv()`, `getAllUnits()`, and `setVillageField()`, generating $O(N \times 4)$ queries per cycle.

#### Market Query Operator Precedence Bug
In `GameEngine/Database/DatabaseMarketQueries.php:273`:
```php
$q = "SELECT * FROM " . TB_PREFIX . "market where vref != $vid and alliance = $alliance or vref != $vid and alliance = 0 and accept = 0";
```
Because SQL `AND` binds with higher precedence than `OR`, the query is parsed by MySQL as:
$$((vref \neq vid \land alliance = alliance)) \lor ((vref \neq vid \land alliance = 0 \land accept = 0))$$
If an offer belongs to the player's alliance (`alliance = $alliance`), the `accept = 0` condition is bypassed entirely, causing already accepted/closed offers to leak into active marketplace views.

#### Concurrency, Locking & Transaction Deficits
- **Lack of Multi-Table Atomicity**:
  - Battles (`AutomationBattleResolution.php:2616-3357`, entrypoint `sendunitsComplete()` at line 2616): Attacks modify defender units, deduct village resources, calculate wall damage, recalculate village loyalty, schedule return movements, and generate combat reports. Despite involving 6 distinct database tables, **no transaction (`START TRANSACTION` / `COMMIT`) wraps the resolution**. An uncaught exception or worker interruption leaves the database permanently desynchronized.
  - Building completion (`AutomationBuildQueue.php:63-122`): Updates `fdata`, alters `vdata` capacity, updates alliance capacity, updates starvation, and deletes from `bdata` using individual disconnected statements without a transaction. If a crash occurs before `DELETE FROM bdata`, buildings upgrade repeatedly on subsequent ticks.
- **Fragmented Locking Mechanisms**:
  User-level MySQL advisory locks (`GET_LOCK(...)`) are inconsistently applied:
  - Present in `DatabaseBuildingQueries.php:120` (`build_village_$wid`)
  - Present in `DatabaseMarketQueries.php:323` (`merchant_$vid`)
  - Present in `DatabaseTroopQueries.php:65` (`research_village_$wid`), `77` (`train_village_$wid`), `89` (`enforce_$id`)
  - Absent across marketplace trade execution, hero resurrection, gold purchases, and village conquer/chieftain loyalty reductions, exposing those operations to race conditions and double-spending.
- **Runtime DDL Table Creation**:
  Multiple feature classes execute runtime DDL during standard HTTP request processing:
  - `GameEngine/GoldShop.php:51, 67` (`CREATE TABLE IF NOT EXISTS gold_promo...`)
  - `GameEngine/MultiAccount.php:91, 105` (`CREATE TABLE IF NOT EXISTS mad_session...`)
  - `GameEngine/PushProtection.php:81, 97` (`CREATE TABLE IF NOT EXISTS push_override...`)
  - `GameEngine/QuestConfig.php:65` (`CREATE TABLE IF NOT EXISTS quest_config...`)
  - `GameEngine/RegBlock.php:57` (`CREATE TABLE IF NOT EXISTS reg_block...`)
  Executing `CREATE TABLE IF NOT EXISTS` at runtime acquires MySQL Metadata Locks (MDL), serializing concurrent incoming queries and creating deadlock spikes under high traffic.

---

### 2.7 Security Vulnerabilities Audit

#### A. SQL Injection Vulnerabilities

##### 1. Dynamic Identifier / Column Injection in Alliance Query
```php
// GameEngine/Database/DatabaseAllianceQueries.php:127-133
function aExist($ref, $type) {
    list($ref, $type) = $this->escape_input($ref, $type);
    $q = "SELECT $type FROM " . TB_PREFIX . "alidata where $type = '$ref'";
    $result = mysqli_query($this->dblink,$q);
    return mysqli_num_rows($result);
}
```
**Vulnerability**: `$type` is interpolated directly into the SQL column position without identifier whitelisting. `escape_input()` uses `mysqli_real_escape_string`, which escapes character string literals but provides zero protection for SQL identifiers.

##### 2. Dynamic Column Injection in Trade Routes
```php
// GameEngine/Database/DatabaseMarketQueries.php:97-106
function editTradeRoute($id,$column,$value,$mode) {
    list($id,$column,$value,$mode) = $this->escape_input((int) $id,$column,(int) $value,$mode);
    if ( ! $mode ) {
        $q = "UPDATE " . TB_PREFIX . "route set $column = $value where id = $id";
    } else {
        $q = "UPDATE " . TB_PREFIX . "route set $column = $column + $value where id = $id";
    }
    return mysqli_query( $this->dblink, $q );
}
```
**Vulnerability**: `$column` is interpolated directly into an `UPDATE ... SET` statement without identifier escaping or column whitelist validation.

##### 3. Dynamic Field Injection in Activation Verification
```php
// GameEngine/Database/DatabaseUserQueries.php:338-345
function getActivateField($ref, $field, $mode) {
    list($ref, $field, $mode) = $this->escape_input($ref, $field, $mode);
    if(!$mode) {
        $q = "SELECT $field FROM " . TB_PREFIX . "activate where id = " . (int) $ref . " LIMIT 1";
    } else {
        $q = "SELECT $field FROM " . TB_PREFIX . "activate where username = '$ref' LIMIT 1";
    }
```
**Vulnerability**: `$field` is interpolated into the SELECT clause, allowing arbitrary subqueries or expressions.

#### B. Authentication & Session Management

##### 1. Disabled Security Middleware
`Security/Security.class.php` declares an input sanitization class, but its singleton instantiator is explicitly commented out:
```php
// Security/Security.class.php:50-58
public static function instance()
{
    if(self::$instance === NULL)
    {
        //return new Security;
    }
    //return self::$instance;
}
```
`index.php:45` calls `Security::instance();`, which returns `NULL` and executes zero sanitization.

##### 2. Password Mutation During Authentication
```php
// GameEngine/Database/DatabaseUserQueries.php:362, 394
list($username, $password) = $this->escape_input($username, $password);
...
$pwOk = password_verify($password, $dbarray['password']);
```
`escape_input` applies `stripslashes` and `mysqli_real_escape_string` to the cleartext `$password` **before** passing it to `password_verify` or `password_hash`. Passwords containing quotes or backslashes are corrupted during comparison.

##### 3. Legacy MD5 Fallback Support
```php
// GameEngine/Database/DatabaseUserQueries.php:396-399
if (!$pwOk && !$dbarray['is_bcrypt']) {
    $pwOk = ($dbarray['password'] == md5($password));
    $bcrypted = false;
}
```
Accounts created prior to bcrypt upgrade remain stored as unsalted MD5 hashes (`users.is_bcrypt = 0`).

##### 4. Session Fixation Vulnerability
A comprehensive search for `session_regenerate_id` across all PHP files returned **0 results**. Neither `Session::login()` (`GameEngine/Session.php:214-250`) nor `Account::Login()` regenerates the PHP session identifier upon credential verification, leaving users vulnerable to session fixation attacks.

##### 5. Static Variable Pollution in Authentication Routine
```php
// GameEngine/Database/DatabaseUserQueries.php:355-360
function login($username, $password) {
    static $cachedResult = null;
    if ($cachedResult !== null) {
        return $cachedResult;
    }
```
The static variable `$cachedResult` caches the authentication outcome within the PHP worker process, returning stale authentication results if invoked across multiple contexts.

#### C. Authorization & Broken Access Control

##### 1. Parameter-Based Admin Impersonation
In `GameEngine/Admin/Mods/recalcWH.php:45-51`, authorization is determined by reading `$_POST['admid']`:
```php
$session = (int)($_POST['admid'] ?? 0);
$id = (int)($_POST['id'] ?? 0);
$admin = $database->getUserArray($session, 1);
if (!$admin || (int)$admin['access'] !== 9) {
    admin_deny('You must be signed in as an administrator...');
}
```
Because `$session` is sourced directly from user-submitted `$_POST['admid']` rather than `$_SESSION['access']`, an unauthenticated client providing `admid=1` (or any valid administrator user ID) bypassed authorization checks in early revisions. While some scripts were patched with `if($_SESSION['access'] < 9)`, authorization logic remains heterogeneous across modules.

##### 2. Flawed Administrative Detection via URI Matching
```php
// GameEngine/Session.php:332-334
$inAdmin = (strpos($_SERVER['REQUEST_URI'], '/Admin') !== false);
$this->inAdmin = $inAdmin;
```
Evaluating whether a user is operating in administrative context via substring matching on `$_SERVER['REQUEST_URI']` allows any player request containing `/Admin` in query parameters (e.g., `dorf1.php?ref=/Admin`) to trigger admin routing branches.

#### D. Cross-Site Scripting (XSS)

##### 1. BBCode Parser Output Without HTML Sanitization
`GameEngine/BBCode.php:44-52` converts BBCode tokens (`[b]`, `[i]`, `[u]`) to HTML via regex substitutions. Crucially, `BBCode.php` does not invoke `htmlspecialchars()` on `$input`. In `Templates/Message/read.tpl:101`, the template renders:
```php
echo stripslashes(nl2br($bbcoded));
```
Any HTML tags embedded in message bodies that bypass input-level sanitization are directly executed.

##### 2. Unescaped Parameter Reflection in Admin Templates
- `Admin/Templates/results_player.tpl:57`:
  `<td><a href="?p=player&uid='.$p['id'].'">'.$p['username'].'</a></td>`
  Renders user-supplied player usernames without `htmlspecialchars()`.
- `Admin/Templates/results_villages.tpl:57-58`:
  `<td><a href="?p=village&did='.$v['wref'].'">'.$v['name'].'</a></td>`
  `<td><a href="?p=player&uid='.$v['owner'].'">'.$owner.'</a></td>`
  Renders village names and owner usernames without escaping.
- `Templates/Profile/graphic.tpl:152`:
  `<input type="text" name="custom_url" value="<?php echo $session->gpack; ?>"`
  Renders the graphics pack path directly into an HTML input attribute without escaping.

#### E. Cross-Site Request Forgery (CSRF)

##### 1. Total Absence of Anti-CSRF Tokens on Core Gameplay Actions
While `Admin/admin.php` and `build_croppers.php` incorporate anti-CSRF token verification (`hash_equals($_SESSION['csrf_cb'], $_POST['csrf'])`), **none of the core player gameplay controllers implement CSRF protection**:
- `a2b.php` / `GameEngine/Units.php:120-150` (sending troop attacks, raids, reinforcements)
- `build.php` / `GameEngine/Building.php` (upgrading buildings, demolishing structures)
- `allianz.php` / `GameEngine/Alliance.php` (disbanding alliances, kicking members, changing permissions)
- `spieler.php` / `GameEngine/Profile.php` (modifying account email, setting sitters, initiating account deletion)
- `plus.php` / `GameEngine/GoldShop.php` (spending account gold)

##### 2. State-Changing GET Requests
Building upgrades and market cancellations rely on GET parameters:
`dorf1.php?a=1&c=...` (`GameEngine/Building.php:330`).
Executing state-altering operations over HTTP GET violates RFC 7231 / RFC 9110 and enables cross-site image tag request attacks (`<img src="https://victim-server/dorf1.php?a=1...">`).

---

### 2.8 Frontend Assets, Templates & Client Scripts

#### Procedural PHP Mixed Directly with HTML
- The repository contains **1,122 PHP/TPL files** (406 `.php` files and 716 `.tpl` files).
- Every `.tpl` file is actually a raw PHP script executing directly via PHP's `include()` or `require()`. There is no templating engine (no Twig, Blade, Smarty, Plates, or Mustache).
- In `dorf1.php:80-118`, global variables, database connections (`$database`), and user sessions (`$session`) are implicitly passed through the global variable scope into included templates:
  ```php
  <?php include("Templates/header.tpl"); ?>
  <div id="mid">
  <?php include("Templates/menu.tpl"); ?>
  <div id="content" class="village1">
  <?php include("Templates/field.tpl");
  include("Templates/movement.tpl");
  include("Templates/production.tpl");
  include("Templates/troops.tpl");
  include("Templates/footer.tpl");
  include("Templates/res.tpl");
  ?>
  ```

#### Obsolete DOCTYPE, Browser Hacks & Table Layouts
- `dorf1.php:47-48`: Pages declare an XHTML 1.0 Transitional DOCTYPE from the year 2000:
  ```html
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  ```
- `dorf1.php:75-77`: Hardcoded browser classes and legacy IE DirectX filters:
  ```html
  <body class="v35 ie ie8">
  <div class="wrapper">
  <img style="filter:chroma();" src="img/x.gif" id="msfilter" alt="" />
  ```
- Extensive use of HTML `<table>` elements for UI layout rather than CSS Flexbox or Grid (`Templates/production.tpl:31-60` with spacer gifs `<img class="r1" src="img/x.gif" />`).
- Deprecated presentational tags: `<font color=gray>`, `<div id="cap" align="left">`, and `<br /><br /><br /><br />` for vertical spacing.

#### 1990s Image Map Technology
Interactive village resource fields and building plots rely on HTML `<map>` and `<area>` image maps:
```php
// Templates/field.tpl:75-155
<map name="rx" id="rx">
...
<area href="build.php?id=1" coords="101,33,28" shape="circle" title="Woodcutter Level 5" />
<area href="dorf2.php" coords="144,131,36" shape="circle" title="Village Centre" />
</map>
```
Coordinate arrays (`$coorarray` in `Templates/field.tpl:36-55`) are hardcoded pixel values (`"101,33,28"`, `"165,32,28"`) that break completely on any non-desktop display resolution or scaling factor.

#### Stylesheet Rigidities, Graphic Packs & Responsiveness
- Primary stylesheet `gpack/travian_default/lang/en/compact.css:42-50` defines rigid fixed desktop widths:
  ```css
  div#header { height: 67px; min-width: 980px; width: 100%; }
  div#mid { float: left; margin-bottom: 15px; min-width: 980px; }
  ```
- There are **no responsive `<meta name="viewport">` tags** on game pages. The UI is locked to an exact desktop viewport of ~980px.
- Graphic packs (`gpack/`) carry heavy legacy debt:
  - `gpack/download/travian_default.zip` (6.18 MB) and `gpack/download/travian_t4.zip` (10.87 MB) are tracked directly inside Git as binary blobs.
  - Unminified CSS files and language-specific CSS directories (`compact.css` at 233 KB, 9,713 lines).

#### Client-Side Script Sprawl and Polling Deficits
- **Competing Legacy JavaScript Bundles**:
  - `mt-core.js`: MooTools 1.2.4 (2006-2009 Valerio Proietti).
  - `mt-full.js`: MooTools 1.2.4 full bundle (117 KB).
  - `mt-more.js`: MooTools More 1.2.4 (28 KB).
  - `uncrypt.js`: MooTools 1.2.5 full unminified script (195 KB, 8,267 lines).
  - `notification/crypt.js`: Another complete copy of MooTools 1.2.5 (147 KB).
  - `AC_OETags.js`: Macromedia Shockwave Flash Player Version Detection Rev 1.6 (2005-2006), referencing ActiveX controls (`ShockwaveFlash.ShockwaveFlash.7`). Flash was permanently end-of-lifed in 2020.
  - `Admin/jquery.cookie.js`: jQuery cookie plugin in `Admin/` despite MooTools being used elsewhere.
  - `GameEngine/Chat.php:23-40`: SAJAX (Simple Ajax Toolkit 0.12 from 2005) bundled directly inside backend PHP code.
- **Unthrottled 30-Second Client-Side Polling Loop (`unx.js:6-14`)**:
  ```javascript
  automation();
  function automation( callback ) {
      if ( typeof callback == 'function' ) {
          xhttp.addEventListener("load", callback);
      }
      xhttp.open("GET", "GameEngine/Automation.php", true);
      xhttp.send();
      setTimeout(function(){ automation(); }, 30000);
  }
  ```
  Every active browser tab executes an HTTP GET to `GameEngine/Automation.php` every 30 seconds using a single global `var xhttp = new XMLHttpRequest();`. Concurrent requests collide on the global instance.
- **Timer Reload Loops**: Countdown timer expirations force full page reloads: `unx.js:34-47`:
  ```javascript
  automation( function() { document.location.reload(); } );
  ```
- **Massive Inline Scripts in Templates**:
  - `Templates/GlobalChat/widget.tpl:20-21` explicitly notes:
    `"Nu depinde de MooTools/jQuery (evita coliziuni cu $ / $$ din unx.js / mt-full.js) - JS vanilla, prefixat 'gchat_' peste tot."`
  - `Templates/GlobalChat/widget.tpl:64-389` embeds 325 lines of inline `<style>`, and lines 390-1222 embed 833 lines of raw inline JavaScript inside a `<script>` tag.

#### Internationalization (i18n) via Global PHP Constants
- `GameEngine/Lang/en.php` (333 KB, 4,445 lines), `es.php` (330 KB), `fr.php` (362 KB), `ro.php` (315 KB).
- All translations are defined as global constants using `tz_def($k, $v)` (`GameEngine/Lang/en.php:27-50`):
  ```php
  tz_def('TRIBE1', 'Romans');
  tz_def('TRAVIAN_PLUS', 'Travian <b><span class="plus_g">P</span><span class="plus_o">l</span><span class="plus_g">u</span><span class="plus_o">s</span></span></span></b>');
  ```
  Once defined, constants cannot be redefined for different locales in the same execution context. Raw HTML markup is baked directly into translation strings, preventing safe auto-escaping.

---

### 2.9 Infrastructure, Server Config & Runtime

#### Web Server Configurations & Deprecated Access Syntax
- The repository root serves as the HTTP document root (`/var/www/html`). All backend PHP classes, templates, logs, and database files reside directly inside the web-accessible directory tree.
- There are **33 `.htaccess` files** scattered throughout the project (`.htaccess`, `Admin/.htaccess`, `var/.htaccess`, and 30 folder-level `.htaccess` files under `Templates/` and `GameEngine/`).
- Root `.htaccess:1-14` uses obsolete Apache 2.2 access control syntax:
  ```apache
  <Files "*.tpl">
      Order Allow,Deny
      Deny from all
  </Files>
  ```
  Under standard Apache 2.4 without `mod_access_compat`, `Order Allow,Deny` is deprecated and causes HTTP 500 configuration errors or fails to restrict access.
- Critical configuration omissions:
  - **No `Options -Indexes`**: Directory listings are not prevented by default.
  - **No front-controller routing**: There is no URL rewriting or central router; every endpoint is a discrete PHP file in the web root.
  - **No protection for root dotfiles and configuration files**: Files like `.env`, `.env.example`, `.dockerignore`, `Dockerfile`, `docker-compose.yml`, `autoloader.php`, and `DOCKER_README.md` are directly exposed if served by an unhardened web server.
- **Zero Nginx configurations**: There are no Nginx server blocks (`nginx.conf`) provided in the repository.

#### Lack of Environment Configuration (.env) in PHP
- The PHP application has **zero integration with environment variables**:
  An audit across all PHP files confirms **0 occurrences of `getenv()` and 0 occurrences of `$_ENV`**.
- `.env.example` exists exclusively to provide environment variables to the MariaDB container in Docker Compose.
- The application relies on `GameEngine/config.php` (which is untracked in Git).
- `config.php` is generated by `install/index.php` using string token replacement on `install/data/constant_format.tpl:430-443`:
  ```php
  define("SQL_SERVER", "%SSERVER%");
  define("SQL_PORT", %SPORT%);
  define("SQL_USER", "%SUSER%");
  define("SQL_PASS", "%SPASS%");
  define("SQL_DB", "%SDB%");
  ```
- Administrative settings screens (`GameEngine/Admin/Mods/editExtraSet.php:134`, `GameEngine/Admin/Mods/editLogSet.php:134`, `GameEngine/Admin/Mods/editNewsboxSet.php:140`) update configuration by reading `GameEngine/config.php` as raw text, executing regex string replacements, and rewriting the PHP file back to disk.

#### Containerization & Docker Setup Deficits
- `Dockerfile`:
  - Based on `php:8.3-apache` (Apache prefork module + `mod_php`), rather than high-performance PHP-FPM + Nginx.
  - Lines 34-35 state: `"Runtime source code is bind-mounted by docker-compose. Keep only minimal folder bootstrap inside the image."` The `Dockerfile` does **not** copy application source code into the container. It cannot be built into an immutable, self-contained production image.
  - Line 39 contains a no-op sed command: `RUN sed -i 's!/var/www/html!/var/www/html!g' /etc/apache2/sites-available/000-default.conf`.
  - Missing extensions standard in modern PHP architectures: `bcmath`, `opcache`, `intl`, `redis`, `apcu`.
- `docker-compose.yml`:
  - MariaDB service (`db`) explicitly forces:
    `command: --sql_mode=""` (`docker-compose.yml:35`).
    This completely disables MariaDB/MySQL strict SQL modes (`STRICT_TRANS_TABLES`, `NO_ZERO_DATE`, `ONLY_FULL_GROUP_BY`).
  - Hardcoded default credentials in compose file: `rootpassword`, `travianzpass`.
  - No Redis or Memcached service for session storage, caching, or distributed locks.
  - No background worker / queue container for the automation tick loop.

---

### 2.10 Testing, QA & CI/CD Pipelines

#### Existing Test Suite Limitations
- **Zero Standard Test Frameworks**: No PHPUnit, Pest, Codeception, or Behat exists in the repository.
- Exactly **9 executable test scripts** exist across the entire project (8 standalone PHP scripts and 1 shell script):
  - `tests/docker-compose-contract.sh`: Shell script using `awk` pattern matching on `docker-compose.yml`.
  - `tests/InstallerImprovementsTest.php`: Inspects source files using `file_get_contents()` and checks for string occurrences (`assert_true(strpos($gitignore, '.workbuddy-ai/') !== false)`).
  - `tests/InstallerRenderTest.php`: Executes `install/index.php` with mock `$_GET['s']` steps and inspects output buffers for string markers.
  - `tests/LanguageLoaderTest.php`: Tests language fallback merging.
  - `tests/MultisortTest.php`: Tests array multisorting.
  - `tests/regression/GetResearchingCacheTest.php`: Mocks `mysqli_query` to test troop research cache.
  - `tests/regression/GetTimeFormatTest.php`: Tests time formatting string output.
  - `tests/regression/RegistrationLanguageTest.php`: Tests registration language selection.
  - `tests/regression/ServerStartTimeTest.php`: Tests start time calculation logic.
- Every test file defines its own ad-hoc assertions (`fail_test()`, `assertSameMultisort()`, `assert_true()`) and manually invokes `exit(1)` or throws `RuntimeException`.
- **0% Automated Test Coverage on Core Game Mechanics**: There are no unit or regression tests for battle calculations (`Battle.php`), catapult damage formulas, resource production rates (`Village.php`), building queues (`Building.php`), or database queries.
- Zero integration tests against a live database. Zero End-to-End (E2E) browser automation tests (Playwright/Cypress).

#### Code Quality & Static Analysis Tooling
- **PHPStan**: None (`phpstan.neon` absent).
- **Psalm**: None (`psalm.xml` absent).
- **Code Linters / Fixers**: No PHP_CodeSniffer (`phpcs.xml`), no PHP-CS-Fixer (`.php-cs-fixer.php`), no Laravel Pint.
- **Rector**: None (`rector.php` absent).
- In `scripts/check.sh:8-25`, "linting" consists solely of running `php -l` (basic syntax verification) in a bash `while` loop over tracked `.php` and `.tpl` files.

#### Minimal CI/CD Pipeline
- Single GitHub Actions workflow: `.github/workflows/quality.yml:1-49`.
- Workflow runs on push/PR to `master` across PHP 8.3, 8.4, and 8.5 matrix on Ubuntu.
- Test step executes `./scripts/check.sh php`:
  ```bash
  lint_php() {
      # Runs `php -l "$file"` across all tracked PHP and TPL files
  }
  run_php_tests() {
      # Executes each *Test.php file directly with CLI `php "$file"`
  }
  ```
- No automated build artifacts, no Docker container image publishing, no security scanning (Dependabot, Trivy, CodeQL), and no code quality gates.

---

## 3. Modernization Strategy & Technology Selection

### 3.1 Target Architecture: Hexagonal / Clean Architecture & API-First

To transform TravianZ from a fragile procedural monolith into a robust, high-performance MMORTS engine, the target architecture adopts **Hexagonal (Ports & Adapters) / Clean Architecture** principles, coupled with an **API-First** presentation strategy.

```
+----------------------------------------------------------------------------------------------------+
|                                         PRESENTATION LAYER                                         |
|  - Dedicated HTTP DocumentRoot (public/index.php)                                                  |
|  - FastRoute / Symfony Routing with PSR-15 Middleware Pipeline:                                    |
|    [Security Headers] -> [RateLimiter] -> [SessionRegenerator] -> [CsrfGuard] -> [AuthMiddleware]   |
|  - Web Controllers (Twig HTML Rendering)  |  API Controllers (JSON:API / REST Endpoints)           |
+----------------------------------------------------------------------------------------------------+
                                                  │
                                                  ▼
+----------------------------------------------------------------------------------------------------+
|                                         APPLICATION LAYER                                          |
|  - Command & Query Handlers (CQRS Pattern via Symfony Messenger)                                    |
|  - Use Cases: UpgradeBuildingCommand, SendAttackCommand, ExecuteMarketTradeCommand                 |
|  - Application DTOs (Data Transfer Objects) validating HTTP inputs                                 |
|  - PSR-11 Dependency Injection Container (PHP-DI / Symfony DI)                                     |
+----------------------------------------------------------------------------------------------------+
                                                  │
                         +------------------------+------------------------+
                         │                                                 │
                         ▼                                                 ▼
+--------------------------------------------------+   +---------------------------------------------+
|                   DOMAIN LAYER                   |   |            INFRASTRUCTURE LAYER             |
|  - Pure Domain Entities & Value Objects:         |   |  - Persistence: Doctrine DBAL / PDO         |
|    Village, Player, TroopStack, BuildingSlot     |   |    (100% Prepared Statements, UTF-8 MB4)    |
|  - Isolated Mathematical Engines (Pure PHP):     |   |  - Typed Repositories:                      |
|    - BattleCalculator (sigma curve, morale)      |   |    DoctrineVillageRepo, DoctrineUserRepo    |
|    - CatapultEngine (damage curves, targets)     |   |  - Event Broker & Async Queue:              |
|    - ProductionEngine (rates, bonuses, oasis)    |   |    Redis 7.x + Symfony Messenger Worker     |
|  - Domain Events:                                |   |  - Distributed Locks: Redis Redlock         |
|    TroopMovementArrivedEvent, BuildingLeveledEvent|  |  - Logging & Metrics: Monolog (PSR-3)       |
+--------------------------------------------------+   +---------------------------------------------+
```

#### Proposed Directory Structure Restructuring
```
TravianZ/
├── bin/                               # CLI Executables
│   ├── console                        # Symfony Console CLI entrypoint
│   └── worker                         # Background queue worker daemon
├── config/                            # Environment-driven Configuration
│   ├── app.php
│   ├── database.php                   # Reads $_ENV / .env via Doctrine DBAL
│   ├── packages/                      # DI, routing, and cache definitions
│   └── routes.php                     # Central HTTP route definitions
├── public/                            # Sole Web-Accessible HTTP DocumentRoot
│   ├── index.php                      # Single Front Controller
│   ├── assets/                        # Compiled Vite assets (versioned hashes)
│   └── favicon.ico
├── src/                               # Application Core (PSR-4: App\)
│   ├── Application/                   # Application Services & CQRS Handlers
│   │   ├── Commands/
│   │   ├── Queries/
│   │   └── DTOs/
│   ├── Domain/                        # Pure Business Logic (No DB/HTTP Dependencies)
│   │   ├── Model/                     # Entities & Value Objects (Village, Army, Unit)
│   │   ├── Combat/                    # BattleCalculator, CatapultEngine, Morale
│   │   ├── Building/                  # BuildingConfig, PrerequisiteChecker
│   │   ├── Economy/                   # ProductionCalculator, WarehouseCapacity
│   │   └── Events/                    # Domain Events
│   ├── Infrastructure/                # Adapters & External Implementations
│   │   ├── Persistence/               # Doctrine DBAL Repositories & Migrations
│   │   ├── Queue/                     # Redis Messenger Transport & Handlers
│   │   ├── Security/                  # Argon2id Hasher, CsrfTokenManager
│   │   └── Logging/                   # Monolog Configuration
│   └── Presentation/                  # HTTP & API Controllers
│       ├── Controller/                # Web & JSON API Controllers
│       ├── Middleware/                # PSR-15 Middleware Stack
│       └── View/                      # Twig Template Bridge
├── templates/                         # Modern Twig Templates (Outside Web Root)
│   ├── base.html.twig
│   ├── village/                       # Overview (dorf1), Centre (dorf2)
│   ├── building/                      # Building views
│   └── combat/                        # Rally point, attack confirmation
├── tests/                             # Automated Test Suites
│   ├── Unit/                          # Pest / PHPUnit Unit Tests (Combat math)
│   ├── Integration/                   # Database & Repository Integration Tests
│   └── E2E/                           # Playwright Browser Tests
├── var/                               # Writable Runtime Cache & Logs (Unexposed)
│   ├── cache/                         # Twig & DI compiled containers
│   └── log/                           # Structured application logs
├── .env.example                       # Documented 12-Factor environment template
├── composer.json                      # PHP Dependency Manifest & Autoloading
├── package.json                       # Frontend Asset Build Manifest (Vite/Tailwind)
├── phpstan.neon                       # Static Analysis Configuration
└── vite.config.js                     # Asset Bundler Configuration
```

---

### 3.2 Modern PHP Upgrade Path (PHP 8.3 / 8.4)

1. **Strict Type Safety Baseline**:
   - Every modern source file begins with `declare(strict_types=1);`.
   - Remove all error suppression directives in `zz-travianz.ini`. Standardize development error reporting to `E_ALL`.
2. **Standardized Composer Dependency Management**:
   - Eliminate `autoloader.php`. Configure standard PSR-4 autoloading alongside a legacy classmap in `composer.json`:
     ```json
     "autoload": {
       "psr-4": {
         "App\\": "src/"
       },
       "classmap": [
         "GameEngine/"
       ]
     },
     "autoload-dev": {
       "psr-4": {
         "App\\Tests\\": "tests/"
       }
     }
     ```
   - *Critical Sequencing Requirement*: Specifying `autoload.classmap` for legacy unnamespaced `GameEngine/` classes alongside PSR-4 `App\` is strictly mandatory. Because `autoloader.php` handles all legacy classes through relative path inspection, replacing it with only PSR-4 would cause immediate fatal class resolution errors (`Class 'MYSQLi_DB' not found`) across all 406 legacy entrypoints. Adding `GameEngine/` to Composer's classmap provides instantaneous, zero-overhead class loading during the transition.
3. **Adoption of Modern PHP 8.3 / 8.4 Language Constructs**:
   - **Typed Properties & Return Types**: Eliminate untyped signatures across domain models.
   - **Constructor Property Promotion**: Streamline value objects and command payloads:
     ```php
     final readonly class TroopStack {
         public function __construct(
             public UnitType $type,
             public int $amount
         ) {}
     }
     ```
   - **Readonly Classes & Enums**: Model static game data (e.g. `Tribe::Romans`, `BuildingType::Barracks`, `MovementType::Attack`) as typed PHP 8.1+ Enums.
   - **Match Expressions**: Replace verbose `switch` statements in combat and resource resolution with exhaustive `match()` expressions.
   - **Explicit Nullability (PHP 8.4 Compliance)**: Ensure all optional parameters utilize explicit nullable syntax (`?string $val = null`).

---

### 3.3 Database Modernization & Concurrency Control

#### Technology Selection: Doctrine DBAL & Phinx/Doctrine Migrations
- **Abandon `MYSQLi_DB`**: Deprecate global `$database`, `$link`, `$GLOBALS['db']`, and direct `mysqli_query` calls.
- **Adopt Doctrine DBAL**:
  - Provides a powerful, typed database abstraction layer with 100% parameterized query execution.
  - Native integration with PDO, supporting connection pooling, nested transactions, and custom types.
  - Strict UTF-8 MB4 (`utf8mb4_unicode_520_ci`) connection encoding.
- **Migration Engine**: Adopt **Doctrine Migrations** (or **Phinx**) to maintain version-controlled schema definitions:
  - Eliminate all runtime `CREATE TABLE IF NOT EXISTS` statements from application code.
  - Provide automated schema up/down migrations for continuous delivery pipelines.

#### Schema Normalization & Constraint Architecture
1. **Normalize `fdata` (80 columns) -> `village_buildings`**:
   ```sql
   CREATE TABLE village_buildings (
       id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
       village_id INT UNSIGNED NOT NULL,
       slot_id TINYINT UNSIGNED NOT NULL,
       building_type TINYINT UNSIGNED NOT NULL,
       level TINYINT UNSIGNED NOT NULL DEFAULT 0,
       is_under_construction BOOLEAN NOT NULL DEFAULT FALSE,
       UNIQUE KEY uq_village_slot (village_id, slot_id),
       KEY idx_building_type (building_type),
       CONSTRAINT fk_vbuildings_village FOREIGN KEY (village_id)
           REFERENCES vdata (wref) ON DELETE CASCADE
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
   ```
2. **Normalize `units` (94 columns) -> `village_units`**:
   ```sql
   CREATE TABLE village_units (
       id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
       village_id INT UNSIGNED NOT NULL,
       unit_type TINYINT UNSIGNED NOT NULL,
       amount INT UNSIGNED NOT NULL DEFAULT 0,
       UNIQUE KEY uq_village_unit (village_id, unit_type),
       CONSTRAINT fk_vunits_village FOREIGN KEY (village_id)
           REFERENCES vdata (wref) ON DELETE CASCADE
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
   ```
3. **Normalize `users` (100 columns across 112 table definition lines)**:
   - Extract authentication and credentials into `user_credentials` (`user_id`, `password_hash`, `email`, `created_at`).
   - Extract sitting arrangements into `user_sitters` (`owner_id`, `sitter_id`, `permissions`).
   - Extract bonus timers into `user_bonuses` (`user_id`, `bonus_type`, `expires_at`).
4. **Mandatory Foreign Key Constraints**:
   - Enforce relational integrity between users, villages, movements, and reports with `ON DELETE CASCADE`.
5. **Universal ACID Transactions & Row-Level Locking**:
   - Wrap all multi-step state mutations (battle resolutions, building completions, market trades) within `Connection::transactional(function($conn) { ... })`.
   - Replace MySQL user advisory locks (`GET_LOCK`) with InnoDB row-level locking via `SELECT ... FOR UPDATE`.

#### Transition Architecture & The MariaDB View Non-Updatability Constraint
A critical architectural constraint in MariaDB/MySQL governs the migration of legacy wide tables:
- **Non-Updatability of Pivoted Views**: Creating a backward-compatible view `fdata_view` that pivots normalized `village_buildings` rows back into 80 legacy columns (`f1..f40`, `f1t..f40t`) requires either conditional aggregation (`MAX(CASE WHEN slot_id = 1 THEN level END) AS f1 ... GROUP BY village_id`) or 40 self-joins (`LEFT JOIN village_buildings b1 ...`). Under MariaDB rules:
  - Aggregated views containing `GROUP BY` are strictly read-only. Any `UPDATE` triggers `ERROR 1288 (HY000): The target table fdata of the UPDATE is not updatable`.
  - Self-joined views cannot modify more than one underlying base table in a single statement. Executing an update modifying both level and type (`UPDATE fdata SET f1 = 10, f1t = 19 WHERE vref = 123`) or multi-slot mutations throws `ERROR 1393 (HY000): Can not modify more than one base table through a join view`.
  - Furthermore, MariaDB does not support `INSTEAD OF` triggers on views (unlike PostgreSQL or Oracle).
- **Mandatory Two-Tier Transition Architecture**:
  To ensure zero disruption to legacy scripts during Phase 2, physical table dropping of `fdata` and `units` is strictly deferred until Phase 3. Instead, a transitional bridge is deployed:
  1. **PHP Data Access Adapter Layer (Primary Strategy)**: Implement `LegacyFdataAdapter` and `LegacyUnitsAdapter` that intercept procedural data access methods (`setVillageField()`, `updateUnits()`, `getVillageField()`). Writes are translated directly into normalized SQL statements targeting `village_buildings` and `village_units`.
  2. **Database Dual-Write Triggers (Fallback Strategy)**: Alternatively, maintain `fdata` and `units` as physical base tables with `AFTER UPDATE` and `AFTER INSERT` database triggers that replicate row mutations to `village_buildings` and `village_units`.
  3. **Safe Decommissioning**: Legacy wide base tables are only dropped in Phase 3 after all write and read paths across procedural scripts and templates have been replaced by domain repositories and Twig views.

---

### 3.4 Decoupled Game Engine & Event-Driven Queue

#### Architectural Decoupling: Removing Web-Request Execution
The legacy dual-pipeline architecture—where player HTTP requests synchronously execute automation ticks upon `cron_active.txt` staleness—must be permanently eliminated:
- Remove lines 478–506 of `GameEngine/Village.php`.
- Remove the client-side 30-second polling loop in `unx.js`.
- HTTP request threads must **never** execute background maintenance or batch combat processing.

#### Message Queue & Asynchronous Worker Architecture
- **Broker**: **Redis 7.x** (or RabbitMQ) acting as a high-performance message broker.
- **Queue Framework**: **Symfony Messenger** component.
- **Event-Driven Scheduled Dispatch**:
  - Rather than sweeping the entire database every 60 seconds with 25 file-locked tasks, state changes become discrete, event-driven messages:
    - When an attack is launched, an `ExecuteMovementMessage` is dispatched to the queue with a scheduled delay matching `arrival_time`.
    - When a building upgrade starts, a `CompleteBuildingMessage` is dispatched with a delay matching `completion_time`.
- **Worker Daemon (`bin/worker`) & Memory Recycling**:
  - Persistent PHP CLI daemons managed via Supervisor or Docker Compose consume messages asynchronously.
  - Battle calculations are processed concurrently on background worker threads without stalling player HTTP responses.
  - *Worker Memory Recycling*: Persistent PHP worker processes accumulate memory over time due to internal static caches, entity identity maps, and DBAL connection buffers. Workers must enforce strict recycling parameters: `php bin/console messenger:consume async --limit=500 --memory-limit=256M`. When a worker reaches 500 messages or 256 MB of RSS memory, it exits gracefully, and Supervisor/Docker restarts a fresh instance within milliseconds.
- **Queue Arrival Serialization by `destination_village_id`**:
  - In competitive Travian play, attack waves are precision-timed to land within the exact same second (e.g., clearing wave landing at 12:00:00 followed by 4 catapult waves at 12:00:00).
  - Unsynchronized parallel workers could race, causing a catapult wave to process before the clearing wave arrives, leading to catastrophic miscalculations.
  - The queue architecture partitions movement execution into Redis streams keyed by `destination_village_id` (or acquires an exclusive Redis lock on `lock:village:destination:{id}` during resolution). This guarantees that all incoming waves targeting a village execute strictly in chronological order (`arrival_time ASC, movement_id ASC`).
- **Distributed Locking via Redis**:
  - Replace the 25 filesystem locks (`GameEngine/Prevention/*.txt`) with Redis-backed distributed locks (`Symfony\Component\Lock\LockFactory` using Redis store).
- **Watchdog Circuit Breaker for Game Loop Transition**:
  - To eliminate the risk of total game freeze if worker containers stall or crash, an automated watchdog monitors queue worker heartbeat in Redis.
  - If event processing lag exceeds 3 minutes (`worker_heartbeat_age > 180s`), the watchdog raises a high-priority operational alert and triggers an emergency supervised catch-up tick (`bin/console game:catchup-ticks`) or engages a temporary maintenance notice.
- **Session-Scoped CSRF Architecture for Multi-Tab Management**:
  - Core player actions (`a2b.php`, `build.php`, `allianz.php`) require robust CSRF protection.
  - Because advanced players routinely open 10–20 browser tabs across different villages simultaneously, rotating single-use tokens on every GET request causes instant token invalidation in previously opened tabs.
  - The CSRF system must implement synchronized, session-scoped cryptographic tokens (stored in Redis user session state), allowing seamless concurrent multi-tab village management without CSRF validation failures.

---

### 3.5 Frontend Modernization & Real-Time Client

1. **Modern Server-Side Templating (Twig)**:
   - Adopt **Twig 3.x** as the presentation engine.
   - Enforce automatic contextual HTML escaping (`{{ var }}`), template inheritance (`base.html.twig`), and macro components.
   - Completely detach template files from global PHP variables and database connections.
2. **Modern Asset Build Pipeline (Vite + Tailwind CSS)**:
   - Introduce **Vite** as the frontend development server and production bundler.
   - Replace the 9,713-line fixed `compact.css` with a responsive **Tailwind CSS** design system.
   - Add responsive `<meta name="viewport" content="width=device-width, initial-scale=1.0">` tags.
3. **Interactive SVG Village Board**:
   - Replace obsolete HTML `<map>`/`<area>` image maps with **inline responsive SVG**.
   - SVG polygons and circles scale seamlessly across 4K monitors and mobile viewports, supporting CSS hover states, smooth animations, and touch events without hardcoded pixel coordinates.
4. **Client-Side Scripting Modernization (TypeScript & ES Modules)**:
   - Permanently delete `mt-core.js`, `mt-full.js`, `mt-more.js`, `uncrypt.js`, `crypt.js`, `AC_OETags.js`, and `Admin/ajax.js`.
   - Author client logic in modern **TypeScript**, compiled into native ES Modules.
   - Replace full browser page reloads (`document.location.reload()`) with reactive client-side timer components that dynamically update UI state via JSON endpoints.
5. **Real-Time Notifications (WebSockets / Mercure)**:
   - Integrate **Mercure Hub** or **WebSockets** for real-time incoming attack alerts, chat updates, and resource ticks, eliminating polling overhead.

---

### 3.6 Tooling, Linters, Static Analysis & CI/CD

1. **Static Analysis (PHPStan)**:
   - Install `phpstan/phpstan`.
   - Configure `phpstan.neon` with an initial Level 3 baseline (`phpstan analyse --generate-baseline`).
   - Progressively ratchet static analysis up to **Level 8**, enforcing strict parameter typing, return types, and null safety across the domain.
2. **Modern Test Framework (Pest PHP / PHPUnit 10+)**:
   - Install `pestphp/pest` for expressive, behavior-driven testing.
   - Migrate the 9 existing test scripts (8 standalone PHP scripts: `MultisortTest.php`, `LanguageLoaderTest.php`, `InstallerImprovementsTest.php`, `InstallerRenderTest.php`, `GetTimeFormatTest.php`, `GetResearchingCacheTest.php`, `RegistrationLanguageTest.php`, `ServerStartTimeTest.php` + 1 shell contract script: `docker-compose-contract.sh`) into standard Pest unit and contract tests.
   - **Architectural Clarification: Pure Mathematical Formulas vs Stateful Combat Logic**:
     - *Stateful Dependency Coupling in `Battle.php`*: The monolithic `Battle::calculateBattle()` entrypoint is tightly coupled to global state (`$database->getArtifactsValueInfluence()`, global arrays `$GLOBALS['h...']`) and cannot be executed as a zero-dependency unit test in Phase 0 without database mocking.
     - *Phase 0 Pure Math Safety Net*: Phase 0 isolates and codifies the **pure deterministic mathematical formulas** into high-speed unit tests:
       * **Non-linear Combat Model**: The classic $\sigma(x)$ ratio curve (`Battle.php:30-32`): $\sigma(x) = \frac{x > 1 ? 2 - x^{-1.5} : x^{1.5}}{2}$.
       * **Catapult Demolition Curve**: Building level reduction math (`calculateCatapultsDamage()`) and triangular demolition formulas ($1 + \dots + n$).
       * **Ram Durability & Wall Reduction**: Wall level defense multipliers and ram impact durability.
       * **Resource Production & Starvation**: Oasis percentage stacking, hero resource production bonuses, warehouse caps, and net crop starvation rates.
       * **Chieftain Loyalty Reductions**: Deterministic validation of base loyalty reductions ($rand(20, 30)$ with Great Celebration modifiers).
     - *Phase 1/2 Stateful Integration Tests*: Stateful battle flows (`AutomationBattleResolution.php:2616-3357`, multi-wave resolution, artifact modifiers) are tested in Phase 1 and Phase 2 using mocked database connections or SQLite in-memory fixtures.
3. **Automated Code Formatting (Laravel Pint / PHP-CS-Fixer)**:
   - Configure **Laravel Pint** (or `friendsofphp/php-cs-fixer`) using the **PER-CS 2.0 / PSR-12** coding standard.
   - Automate code formatting checks in pre-commit hooks and CI pipelines.
4. **Production CI/CD Matrix (GitHub Actions)**:
   - Modernize `.github/workflows/quality.yml` into a four-stage quality gate:
     1. **Code Style Gate**: `pint --test` (fails on PSR-12 formatting violations).
     2. **Static Analysis Gate**: `phpstan analyse --error-format=github`.
     3. **Automated Test Gate**: `pest --coverage --min=80`.
     4. **Container Build & Security Gate**: Builds multi-stage Docker image and executes Trivy vulnerability scanning.

---

## 4. Phased Modernization Roadmap (Phases 0 through 5)

```
ROADMAP TIMELINE & DEPENDENCY GRAPH:

+-------------------------------------------------------------+
| Phase 0: Tooling, Safety Net & Core Test Harness            |
| (Composer, Pest, PHPStan Baseline, Combat Math Unit Tests)   |
+-------------------------------------------------------------+
                              │
                              ▼
+-------------------------------------------------------------+
| Phase 1: Infrastructure, Config & Container Foundations     |
| (Public DocumentRoot, .env, MariaDB Strict Mode, Docker)    |
+-------------------------------------------------------------+
                              │
                              ▼
+-------------------------------------------------------------+
| Phase 2: Database Layer Modernization & Security Hardening  |
| (Doctrine DBAL, Migrations, Transactions, CSRF, Argon2id)   |
+-------------------------------------------------------------+
                              │
                              ▼
+-------------------------------------------------------------+
| Phase 3: Domain Extraction & Global State Elimination       |
| (Deconstruct God Objects, Eliminate $GLOBALS, PSR-11 DI)   |
+-------------------------------------------------------------+
                              │
                              ▼
+-------------------------------------------------------------+
| Phase 4: Decoupled Game Loop & Asynchronous Event Engine    |
| (Redis Queue, Symfony Messenger, Remove Web Fallback)       |
+-------------------------------------------------------------+
                              │
                              ▼
+-------------------------------------------------------------+
| Phase 5: Presentation Modernization, Twig & Modern Frontend |
| (Front Controller, FastRoute, Twig, Vite + Tailwind, SVG)  |
+-------------------------------------------------------------+
```

---

### 4.1 Phase 0: Tooling, Safety Net & Core Test Harness

#### Scope & Key Deliverables
- Initialize `composer.json` with PSR-4 autoloading (`App\` -> `src/`) and an explicit `autoload.classmap` covering legacy unnamespaced `GameEngine/` classes, alongside `autoload-dev` (`App\Tests\` -> `tests/`).
- Install developer tooling: `pestphp/pest`, `phpstan/phpstan`, `laravel/pint`.
- Establish `phpstan.neon` with an initial baseline capturing existing legacy errors.
- **Isolate & Codify Pure Mathematical Game Rules into Zero-Dependency Unit Tests**:
  - Non-linear combat ratio curve: The $\sigma(x)$ model (`Battle.php:30-32`): $\sigma(x) = \frac{x > 1 ? 2 - x^{-1.5} : x^{1.5}}{2}$.
  - Catapult building demolition curves (`calculateCatapultsDamage()`) and triangular demolition math ($1 + \dots + n$).
  - Ram wall durability curves and wall level defense multipliers.
  - Resource production math, hero resource bonuses, oasis stacking multipliers, warehouse capacity thresholds, and net crop starvation formulas.
  - Chieftain loyalty reduction ranges ($rand(20, 30)$ with Great Celebration modifiers).
  *(Note: Monolithic `Battle::calculateBattle()` is coupled to `$database` and global state; stateful combat integration testing belongs to Phase 1/2 with mocked DB or in-memory fixtures).*
- Replace the custom, exception-throwing `autoloader.php` with Composer's optimized autoloader (backed by `autoload.classmap` to guarantee zero class resolution regressions across all 406 legacy entrypoints).
- Convert all 9 existing test scripts (8 standalone PHP scripts: `MultisortTest.php`, `LanguageLoaderTest.php`, `InstallerImprovementsTest.php`, `InstallerRenderTest.php`, `GetTimeFormatTest.php`, `GetResearchingCacheTest.php`, `RegistrationLanguageTest.php`, `ServerStartTimeTest.php` + 1 shell contract script: `docker-compose-contract.sh`) into standard Pest unit and contract tests.

#### Prerequisites & Dependencies
- None. Can be executed immediately on the existing codebase without disrupting legacy runtime behavior.

#### Technical & Operational Risks with Mitigation Strategies
- *Risk*: Modifying `autoloader.php` breaks legacy scripts that rely on 5-level relative path directory traversal or unnamespaced `GameEngine/` classes.
  - *Mitigation*: Configure Composer `autoload.classmap` covering `GameEngine/` alongside PSR-4 `App\`. This guarantees instantaneous, zero-overhead class loading for all existing procedural classes before namespaces are introduced.
- *Risk*: Battle formulas in `Battle.php` produce slight floating-point discrepancies across PHP versions.
  - *Mitigation*: Author tests using high-precision assertions (`assertEqualsWithDelta($expected, $actual, 0.0001)`) using golden fixture data extracted from verified combat runs.
- *Risk*: Attempting to execute monolithic battle methods directly in unit tests fails due to missing database connections.
  - *Mitigation*: Strictly separate Level 1 pure mathematical formulas (zero-dependency) from Level 2 stateful integration tests (which require database test doubles in Phase 1/2).

#### Definition of Done (DoD)
- `composer.json` and `composer.lock` committed to the repository.
- `vendor/autoload.php` cleanly resolves all classes without `autoloader.php`.
- `composer test` executes all unit tests with 100% pass rate.
- `composer stan` executes PHPStan without reported errors against baseline.
- `composer lint` verifies PSR-12 code style compliance on all new files.

#### Objective Verification Criteria
```bash
composer install
composer test
composer stan
composer lint
```

---

### 4.2 Phase 1: Infrastructure, Configuration & Container Foundations

#### Scope & Key Deliverables
- Re-architect repository directory structure to introduce a dedicated `public/` DocumentRoot.
- Move static media assets (`img/`, `gpack/`) into `public/assets/`.
- Deploy a **Transitional Front Controller / Nginx Rewrite Bridge**:
  - Configure Nginx FastCGI rewrites that transparently route incoming requests for legacy root scripts (`dorf1.php`, `dorf2.php`, `build.php`, `a2b.php`, etc.) directly to legacy root entrypoints (`fastcgi_param SCRIPT_FILENAME $document_root/../$fastcgi_script_name;`) while serving static assets from `public/assets/`, preserving legacy script accessibility until Phase 5 front controller routing is deployed.
- Introduce 12-Factor environment configuration via `vlucas/phpdotenv`.
- Replace dynamic rewriting of `GameEngine/config.php` with structured configuration reading `$_ENV`.
- Build a production-ready, multi-stage Docker environment:
  - Web Server: Nginx 1.25+ Alpine reverse proxy.
  - PHP Runtime: PHP 8.3/8.4-FPM with OPcache, `bcmath`, `pdo_mysql`, `intl`, `redis`.
  - Cache/Broker: Redis 7.x service.
  - Database: MariaDB 11 with strict SQL mode enabled.
  - **Dedicated CLI Cron Service Container (`travianz-cron`)**: Add an explicit `cron` service container to `docker-compose.yml` executing `php cron.php` every 60 seconds. This guarantees that background automation ticks fire deterministically in containerized local and staging environments, preventing reliance on the fragile web-request fallback in `Village.php` prior to Phase 4.
- Re-enable standard MariaDB strict SQL modes (`STRICT_TRANS_TABLES`, `ONLY_FULL_GROUP_BY`) and fix immediate non-conforming schema defaults (`DEFAULT ''`, `DEFAULT 0`, `DEFAULT CURRENT_TIMESTAMP`).
- Remove all error suppression directives in `docker/php/zz-travianz.ini`.
- Lay the architectural foundation for session-scoped CSRF tokens to ensure multi-tab browsing compatibility.

#### Prerequisites & Dependencies
- Phase 0 complete (Composer active, classmap operational, test harness verified).

#### Technical & Operational Risks with Mitigation Strategies
- *Risk*: Enabling MariaDB strict mode causes legacy queries to fail due to missing default values or invalid dates (`0000-00-00`).
  - *Mitigation*: Update `var/db/struct.sql` table definitions with explicit default values (`DEFAULT ''`, `DEFAULT 0`, `DEFAULT CURRENT_TIMESTAMP`) and run schema patch scripts before enforcing strict mode in production.
- *Risk*: Moving DocumentRoot to `public/` breaks root `.php` script execution and asset paths in legacy templates.
  - *Mitigation*: Deploy the transitional Nginx rewrite/alias bridge and fallback rules in `public/index.php` that map root scripts to parent paths and redirect legacy asset paths (`/img/*`, `/gpack/*`) to `/assets/*`.
- *Risk*: Game loop stalls in headless container environments without player web requests.
  - *Mitigation*: The dedicated `travianz-cron` container in `docker-compose.yml` guarantees that background automation ticks fire deterministically regardless of external host cron configuration.

#### Definition of Done (DoD)
- Web application boots cleanly under Nginx + PHP-FPM with DocumentRoot set to `public/`.
- All 33 legacy `.htaccess` files deleted and replaced by a single declarative Nginx configuration.
- MariaDB operates with default strict SQL mode enabled (`--sql_mode=""` removed).
- Container logs show zero `E_WARNING` or `E_DEPRECATED` notices during standard page browsing.

#### Objective Verification Criteria
```bash
docker compose up -d --build
curl -I http://localhost:8080/dorf1.php
docker compose exec db mariadb -u root -p -e "SELECT @@sql_mode;"
# Output must show STRICT_TRANS_TABLES and ONLY_FULL_GROUP_BY
```

---

### 4.3 Phase 2: Database Layer Modernization & Security Hardening

#### Scope & Key Deliverables
- Integrate **Doctrine DBAL** to replace `MYSQLi_DB` and raw `mysqli_query` calls.
- Integrate **Doctrine Migrations** (or Phinx); convert `var/db/struct.sql` into baseline migration.
- Eliminate all runtime `CREATE TABLE IF NOT EXISTS` DDL statements from application code (`GoldShop`, `MultiAccount`, `PushProtection`).
- Implement normalized relational schemas:
  - Migrate `fdata` (80 columns) to `village_buildings`.
  - Migrate `units` (94 columns) to `village_units`.
  - Implement a PHP **Data Access Adapter Layer** (`LegacyFdataAdapter`, `LegacyUnitsAdapter`) alongside transitional dual-write mechanisms to intercept legacy array-based writes and route mutations to normalized schemas without breaking un-migrated scripts.
- Enforce foreign keys with `ON DELETE CASCADE` across all core relational entities.
- Wrap all state-changing operations (combat, building upgrades, market transactions) in strict ACID transactions.
- Enforce **Deterministic Entity Row-Locking Order** across multi-village transactions (`SELECT ... FOR UPDATE WHERE vref IN (LEAST(v1, v2), GREATEST(v1, v2)) ORDER BY vref ASC`) to eliminate MariaDB Error 1213 deadlocks under concurrent attacks.
- Security Hardening:
  - Upgrade password storage to `PASSWORD_ARGON2ID` (with fallback to `PASSWORD_BCRYPT` cost 12).
  - Stop passing cleartext passwords through `stripslashes()` or `escape_input()`.
  - Implement mandatory `session_regenerate_id(true)` upon successful user authentication.
  - Implement universal **Session-Scoped Anti-CSRF Token** verification across all gameplay forms (`a2b.php`, `build.php`, `allianz.php`), ensuring multi-tab player workflows remain uninterrupted.
  - Fix SQL injection vulnerabilities in `aExist`, `editTradeRoute`, `getActivateField`.
  - Sanitize all BBCode inputs with `htmlspecialchars($input, ENT_QUOTES, 'UTF-8')`.

#### Prerequisites & Dependencies
- Phase 1 complete (strict database runtime, CLI cron container operational, and Composer classmap active).

#### Technical & Operational Risks with Mitigation Strategies
- *Risk*: Multi-column pivoted views (`fdata_view`, `units_view`) are strictly non-updatable in MariaDB/MySQL (`ERROR 1288`/`1393`), and MariaDB lacks `INSTEAD OF` triggers. Any un-migrated procedural script executing `UPDATE` or `INSERT` on a pivoted view triggers fatal database errors.
  - *Mitigation*: Physical legacy base tables (`fdata`, `units`) are retained during Phase 2. The PHP Data Access Adapter layer intercepts calls to `setVillageField()` and `updateUnits()` and translates them into normalized SQL against `village_buildings` and `village_units` (or database triggers replicate mutations bidirectionally). Dropping legacy base tables is deferred until Phase 3 after domain repositories replace all procedural call sites.
- *Risk*: Concurrent attacks between players create database deadlocks (`ERROR 1213: Deadlock found when trying to get lock`).
  - *Mitigation*: Multi-village transactions must always acquire row locks in deterministic ascending primary key order: `SELECT ... FOR UPDATE WHERE vref IN (LEAST(v1, v2), GREATEST(v1, v2)) ORDER BY vref ASC`.
- *Risk*: Multi-tab village management breaks if CSRF tokens rotate on every GET request.
  - *Mitigation*: Implement synchronized, session-scoped CSRF tokens (persisted in session storage) rather than single-use nonces, permitting players to execute actions across multiple browser tabs simultaneously.
- *Risk*: Password verification breaks for users with backslashes or quotes in passwords due to removing `stripslashes`.
  - *Mitigation*: Implement a dual-check fallback during authentication: verify raw password first; if verification fails, verify `stripslashes($password)` and auto-rehash upon success.

#### Definition of Done (DoD)
- 100% of newly written database queries use Doctrine DBAL prepared statements.
- `struct.sql` replaced by executable versioned migrations.
- Zero occurrences of `session_regenerate_id` omission on login.
- Automated security test suite verifies CSRF token rejection on forged requests to `a2b.php` and `build.php`.
- Zero raw `CREATE TABLE` calls executed during HTTP request handling.

#### Objective Verification Criteria
```bash
composer exec phpunit tests/Security/CsrfProtectionTest.php
composer exec phpunit tests/Security/SqlInjectionRegressionTest.php
composer exec phpunit tests/Database/TransactionIsolationTest.php
```

---

### 4.4 Phase 3: Domain Extraction, God Object Deconstruction & State Elimination

#### Scope & Key Deliverables
- Disassemble the `MYSQLi_DB` God Object (15 traits) into discrete, typed domain repositories:
  `UserRepository`, `VillageRepository`, `BuildingRepository`, `MovementRepository`, `AllianceRepository`.
- Disassemble the `Automation` God Object (14 traits) into decoupled domain services:
  `BattleService`, `BuildingUpgradeService`, `MarketService`, `StarvationService`.
- Eradicate global procedural state variables:
  Remove all dependencies on `$GLOBALS['db']`, `$GLOBALS['link']`, `global $database`, `global $session`, `global $village`.
- Transform procedural global arrays (`$bid1..45`, `$u1..100`, `$unitsbytype`) into immutable, typed Domain Configurations:
  `BuildingConfigRepository`, `UnitConfigRepository`.
- Introduce a PSR-11 compliant Dependency Injection Container (PHP-DI or Symfony DI).
- Implement Command and Query Handlers (CQRS) for core gameplay operations:
  `UpgradeBuildingHandler`, `DispatchTroopsHandler`, `CancelTradeHandler`.

#### Prerequisites & Dependencies
- Phase 2 complete (Doctrine DBAL and transactions operational).

#### Technical & Operational Risks with Mitigation Strategies
- *Risk*: Deconstructing God Objects breaks indirect trait dependencies and undocumented method calls.
  - *Mitigation*: Keep deprecated delegating wrapper methods on `MYSQLi_DB` that forward calls to new repositories, marking them `@deprecated` and tracking call sites via Monolog deprecation logs.
- *Risk*: Memory overhead from DI container compilation.
  - *Mitigation*: Configure container compilation in production (`var/cache/container.php`) with OPcache optimization.

#### Definition of Done (DoD)
- Domain entities (`Village`, `User`, `TroopStack`) have zero dependencies on database connections or HTTP superglobals.
- Zero occurrences of `global $database` in modern `src/` classes.
- Unit tests can instantiate domain services with mocked repositories in < 5ms without database connections.

#### Objective Verification Criteria
```bash
composer exec phpunit tests/Domain/Combat/BattleCalculatorTest.php
composer exec phpunit tests/Domain/Building/BuildingUpgradeTest.php
composer exec phpstan analyse --level=8 src/Domain
```

---

### 4.5 Phase 4: Decoupled Game Loop & Asynchronous Event Engine

#### Scope & Key Deliverables
- Permanently excise web-request execution fallback from `GameEngine/Village.php:478-506`.
- Permanently remove the unthrottled 30-second client-side polling ping from `unx.js:6-14`.
- Delete all 25 sequential filesystem locks in `GameEngine/Prevention/*.txt`.
- Implement an asynchronous event-driven task queue using **Symfony Messenger** and **Redis 7.x**:
  - `ProcessMovementCommand`: Scheduled message dispatched upon troop departure, executing precisely at `arrival_time`.
  - `ProcessBuildingUpgradeCommand`: Scheduled message executing precisely at building completion.
- Deploy a high-performance background CLI worker daemon (`bin/worker`) managed by Docker Compose / Supervisor.
- **Worker Memory Recycling**: Configure persistent CLI worker processes with strict execution caps: `php bin/console messenger:consume async --limit=500 --memory-limit=256M`. When a worker reaches 500 messages or 256 MB of RSS memory, it exits cleanly, allowing Supervisor/Docker to restart a fresh process instantly.
- **Queue Arrival Serialization by `destination_village_id`**:
  - Partition movement execution across Redis streams keyed by `destination_village_id` (or acquire exclusive distributed Redis locks on `lock:village:destination:{id}`).
  - Guarantees that concurrent workers never race or reorder waves targeting the same village: clearing waves land and resolve defender casualties strictly before accompanying catapult or chief waves execute, preserving exact chronological order (`arrival_time ASC, movement_id ASC`).
- **Command Handler Idempotency & State Validation**:
  - Handlers verify database entity state inside transactions before applying mutations. If an attack was recalled within the 90-second window or a building was completed via Gold instant completion, the delayed message is discarded safely without duplicate execution.
- **Watchdog Circuit Breaker Mechanism**:
  - Deploy an automated watchdog service monitoring queue worker heartbeat in Redis.
  - If event processing lag exceeds 3 minutes (`worker_heartbeat_age > 180s`), raise a critical alert and execute an emergency supervised catch-up tick runner (`bin/console game:catchup-ticks`) to prevent world stall.
- Implement distributed locking via Redis (`Symfony\Component\Lock`) to prevent race conditions during concurrent village updates.
- Introduce health monitoring and queue metrics via Prometheus / health check endpoints.

#### Prerequisites & Dependencies
- Phase 3 complete (Domain services extracted and decoupled from global state).

#### Technical & Operational Risks with Mitigation Strategies
- *Risk*: Queue worker crash or failure halts the entire game world after web fallback is excised.
  - *Mitigation*: The watchdog circuit breaker monitors worker heartbeat; if lag exceeds 3 minutes, it alerts operators and triggers emergency supervised catch-up ticks. Worker processes are supervised by Docker/Supervisor with automatic exponential restart policies.
- *Risk*: Concurrent queue workers process multi-wave attacks out of order (catapults hitting before clearing waves).
  - *Mitigation*: Stream partitioning and village-level destination locking (`lock:village:destination:{id}`) ensure all movements targeting a given village are processed strictly serially in ascending order of arrival timestamp and movement ID.
- *Risk*: Daemon memory leaks cause worker crashes during heavy combat load.
  - *Mitigation*: Enforce `--limit=500 --memory-limit=256M` recycling flags on worker daemons, and clear Doctrine identity maps between message executions.
- *Risk*: Redis instance failure halts game progression.
  - *Mitigation*: Configure Redis AOF (Append-Only File) persistence with RDB snapshots and automated health probe alerts.

#### Definition of Done (DoD)
- Player page loads trigger exactly zero background automation tasks or filesystem locks.
- Concurrency test with 500 simultaneous attacks achieves zero deadlocks and zero duplicate troop movements.
- Queue processing latency for scheduled events is < 100ms from scheduled execution timestamp.

#### Objective Verification Criteria
```bash
docker compose exec worker php bin/console messenger:consume async --limit=100
composer exec phpunit tests/Integration/Queue/TroopMovementQueueTest.php
composer exec phpunit tests/Concurrency/RaceConditionStressTest.php
```

---

### 4.6 Phase 5: Presentation Modernization, Routing, Twig & API-First Frontend

#### Scope & Key Deliverables
- Deploy a single Front Controller (`public/index.php`) with FastRoute / Symfony Routing.
- Implement a PSR-15 Middleware Pipeline:
  - Security Headers (HSTS, CSP, X-Frame-Options, X-Content-Type-Options).
  - Rate Limiter (token bucket rate limiting via Redis).
  - Session Regeneration & Sitter Authentication Guard.
  - Centralized CSRF Protection Guard.
- Migrate 716 legacy `.tpl` files into modern **Twig 3.x** templates (`templates/*.html.twig`):
  - Base layout inheritance (`base.html.twig`).
  - Automatic contextual XSS escaping.
  - Component macros for resource bars, troop rosters, and building lists.
- Replace HTML `<map>`/`<area>` image maps with responsive **interactive SVG components**.
- Replace the 9,713-line fixed `compact.css` with a responsive **Tailwind CSS** design system.
- Build modern **TypeScript ES Modules**; permanently remove MooTools, SAJAX, and Flash detection scripts.
- Expose a clean, versioned **JSON:API / REST Endpoints** (`/api/v1/villages/{id}`, `/api/v1/movements`) to enable decoupled SPA/mobile client applications.
- Integrate WebSockets (or Mercure) for real-time game notifications and timer countdowns.

#### Prerequisites & Dependencies
- Phase 4 complete (Async queue operational, domain decoupled).

#### Technical & Operational Risks with Mitigation Strategies
- *Risk*: Complete frontend rewrite alienates players accustomed to classic Travian T3.6 aesthetics.
  - *Mitigation*: Author Tailwind CSS themes that perfectly replicate the classic T3.6 visual design, sprites, and typography while utilizing modern responsive layout mechanics underneath.
- *Risk*: Big-bang template conversion introduces visual regressions.
  - *Mitigation*: Deploy a dual-rendering bridge allowing individual routes to migrate to Twig progressively while maintaining legacy views for un-migrated edge pages.

#### Definition of Done (DoD)
- All 406 legacy root `.php` entrypoint scripts removed or converted to routed controllers.
- Zero raw PHP execution inside presentation files (`.tpl` eradicated; 100% Twig).
- Responsive mobile layout renders cleanly across mobile viewports without horizontal scrolling.
- Playwright E2E test suite executes full user journey: Registration -> Tutorial -> Resource Field Upgrade -> Troop Dispatch -> Market Trade.

#### Objective Verification Criteria
```bash
npm run build
composer exec phpunit tests/E2E/FullUserJourneyTest.php
npx playwright test
```

---

## 5. Actionable Implementation Blueprint & Verification Matrix

The following matrix provides concrete, executable verification commands to independently audit every dimension of the codebase and validate each modernization milestone:

| Audit Dimension | Target Defect / Pattern | Verification Command (PowerShell / Bash) | Expected Baseline Output | Modernization Target Output |
| :--- | :--- | :--- | :--- | :--- |
| **Directory Layout** | Direct DocumentRoot Exposure | `Select-String -Path Dockerfile -Pattern "WORKDIR"` | `WORKDIR /var/www/html` | `WORKDIR /var/www/html/public` |
| **Dependency Tooling** | Absence of Composer | `Get-ChildItem -Recurse -Filter "composer.json"` | *Empty (0 files)* | `composer.json` found in root |
| **PHP Runtime** | Error Suppression in INI | `Select-String -Path docker/php/zz-travianz.ini -Pattern "error_reporting"` | `E_ALL & ~E_DEPRECATED...` | `error_reporting = E_ALL` |
| **Database Strictness** | MariaDB Non-Strict Mode | `Select-String -Path docker-compose.yml -Pattern "--sql_mode"` | `command: --sql_mode=""` | *Directive deleted (strict mode default)* |
| **Autoloading Flaw** | Autoloader Exception Flaw | `Select-String -Path autoloader.php -Pattern "throw new Exception"` | `throw new Exception(...)` | *File deleted (Composer PSR-4)* |
| **Global State** | Side-Effect Database Instantiation | `Select-String -Path GameEngine/Database.php -Pattern "\$database = new"` | `$database = new MYSQLi_DB(...)` | *File deleted (PSR-11 DI Injection)* |
| **Global Arrays** | Procedural Unit Configs | `Select-String -Path GameEngine/Data/unitdata.php -Pattern '\$u1='` | Lines defining `$u1`..`$u100` arrays | *Replaced by UnitConfigRepository* |
| **Game Loop Flaw** | Web-Request Automation Fallback | `Select-String -Path GameEngine/Village.php -Pattern "cron_active.txt"` | Lines checking cron timestamp | *Code excised completely* |
| **Sequential Locks** | 25 File Locks in Tick | `Select-String -Path GameEngine/Automation.php -Pattern "methodsArrays"` | Array of 25 sequential tasks | *Replaced by Redis Task Queue* |
| **Schema Integrity** | Absence of Foreign Keys | `Get-ChildItem -Recurse -Filter "*.sql" \| Select-String -Pattern "FOREIGN KEY"` | *Empty (0 occurrences)* | Foreign keys across all relations |
| **Table Design** | Wide 80-Column `fdata` | `(Get-Content var/db/struct.sql \| Select-String -Pattern "f\d+t.*tinyint").Count` | `41` occurrences (`f1t`..`f40t`, `f99t`) | Normalized `village_buildings` table |
| **Data Access** | Raw `mysqli_query` Calls | `(Get-ChildItem -Recurse -Filter "*.php" \| Select-String -Pattern "mysqli_query").Count` | `~699` occurrences | `0` occurrences (100% DBAL) |
| **Dead Code** | Unused `query_new` Prototype | `Get-ChildItem -Recurse -Filter "*.php" \| Select-String -Pattern "query_new\("` | Definitions in `DatabaseConnectionCore` & `IDbConnection`; 1 uncalled prototype in `User::exists` | *All occurrences deleted (100% DBAL)* |
| **Security (SQLi)** | Dynamic Column Injection | `Select-String -Path GameEngine/Database/DatabaseAllianceQueries.php -Pattern "SELECT \\\$type"` | `SELECT $type FROM ...` | Parameterized typed query |
| **Security (Session)** | Session Fixation Flaw | `(Get-ChildItem -Recurse -Filter "*.php" \| Select-String -Pattern "session_regenerate_id").Count` | `0` occurrences | Present on every authentication path |
| **Security (CSRF)** | Omission on Troop Attacks | `Select-String -Path GameEngine/Units.php -Pattern "csrf"` | *Empty (0 occurrences)* | Verified CSRF middleware token |
| **Security (XSS)** | Unescaped BBCode Output | `Select-String -Path Templates/Message/read.tpl -Pattern "bbcoded"` | `echo stripslashes(nl2br(...))` | Auto-escaped Twig template expression |
| **Frontend Layout** | Obsolete XHTML DOCTYPE | `Select-String -Path dorf1.php -Pattern "DTD XHTML 1.0"` | XHTML 1.0 Transitional | `<!DOCTYPE html>` (HTML5) |
| **Frontend Map** | Hardcoded Image Maps | `Select-String -Path Templates/field.tpl -Pattern "<map name="` | `<map name="rx" id="rx">` | Interactive `<svg viewBox="...">` |
| **Frontend Script** | 30s Client Polling Ping | `Select-String -Path unx.js -Pattern "setTimeout\(function\(\)\{ automation\(\); \}, 30000\);"` | Found in `unx.js:13` | *Script deleted (WebSockets)* |
| **Legacy JS Debt** | MooTools & Flash Detectors | `Get-ChildItem -Recurse -Filter "*.js" \| Select-String -Pattern "MooTools\|ShockwaveFlash"` | Multiple hits across 5 files | *All deleted; TypeScript ES Modules* |
| **Server Config** | Deprecated Apache Directives | `Select-String -Path .htaccess -Pattern "Order Allow,Deny"` | Found in root `.htaccess` | *All 33 .htaccess deleted (Nginx)* |
| **Environment** | Absence of `$_ENV` in PHP | `(Get-ChildItem -Recurse -Filter "*.php" \| Select-String -Pattern "getenv\s*\(|\$_ENV").Count` | `0` occurrences | Environment config via `phpdotenv` |
| **Quality Gates** | Absence of PHPStan & Pest | `Test-Path phpstan.neon`, `Test-Path tests/Pest.php` | `False`, `False` | `True`, `True` (Passing in CI) |

---
*End of Master Modernization Assessment and Engineering Roadmap.*
