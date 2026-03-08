# Release Manager - WordPress Plugin Release Tool

This is a guide for Claude instances working on the Release Manager tool, a web-based utility for releasing WordPress plugins (free via SVN and pro via Git/Zip).

## Quick Start

### Project Overview
- **Tool Name**: Release Manager
- **Purpose**: Web UI to scan plugin directories, validate release readiness, and push releases to WordPress.org (SVN) or package pro plugins (Git/Zip)
- **Architecture**: Standalone PHP web app (no WordPress dependency) with Bootstrap 3 frontend and jQuery AJAX
- **Entry Point**: `/index.php` (includes `header.php` -> `config.php` -> all includes)
- **Environment**: Apache/PHP, uses `shell_exec`/`exec` for git/svn/zip CLI operations

### Key Files
- `/config.php` - Bootstrap: constants, HOME env, includes all libs, loads custom config
- `/index.php` - Main UI: scans plugin dirs, validates metadata, renders plugin cards
- `/ajax.php` - AJAX handler: `release_free_plugin` (SVN tag) and `package_pro_plugin` (Git zip)
- `/header.php` - HTML head with Bootstrap 3.3.1, jQuery, app assets
- `/footer.php` - HTML footer
- `/conf/config.custom.php` - User-specific config (SVN creds, scan dirs, pro release dir)
- `/conf/sample.config.custom.php` - Sample custom config template

### Include Classes
- `/includes/file.php` - `App_Release_Manager_File` — findBinary, archive (zip), readFilePartially, findMainPluginFile, parsePluginMeta
- `/includes/release.php` - `App_Release_Manager_Release` — getRelease/setRelease (version tracking via `zzz_release.txt`), initEnv (git env vars)
- `/includes/string.php` - `App_Release_Manager_String` — msg() for status messages (ok/warn/notice with glyphicons)
- `/includes/wp_lib.php` - `App_Release_Manager_WP_Lib` — parse() for pro plugin metadata, findProReleaseDir()
- `/includes/ajax.php` - `App_Release_Manager_Ajax` — isAjax(), sendJSON() with JSONP support

### Assets
- `/assets/main.js` - jQuery click handlers for Push Release / Package Pro Release buttons
- `/assets/main.css` - Status classes (.ok, .warn, .notice), plugin_container, release_container styles
- `/share/` - Bootstrap 3.3.1 dist, jQuery 2.1.1

### Data
- `/data/latest_wp_ver.txt` - Cached latest WP version (auto-refreshed every 4h)
- `/data/.htaccess` - Directory protection

## Development Commands

```bash
# No build step — plain PHP served by Apache
# No composer — no dependencies
# No tests — manual testing via browser
```

## Coding Style Rules

- **NEVER stack function calls** — resolve to a variable first, then pass it. This includes `define()`, `trim(shell_exec(...))`, etc.
- **NEVER use inline function calls** in string concatenation or arguments — always resolve to a variable first (e.g., `$bin_name_esc = escapeshellarg($bin_name);` then use `$bin_name_esc`)
- **Use double-quoted strings** with variable interpolation instead of concatenation — `"which $bin_name_esc 2>/dev/null"` not `'which ' . $bin_name_esc . ' 2>/dev/null'`
- **Use `_esc` suffix** for escaped variables — `$bin_name_esc`, `$file_esc`, etc.
- **NEVER use closures/anonymous functions** — always use named methods
- **NEVER modify source data** — don't use pass-by-reference (`&$param`). Return the modified copy instead
- **HTML in PHP strings**: double quotes outside, single quotes inside with variable interpolation — `"<a href='$url_esc'>$label_esc</a>"` — always escape variables before interpolation into HTML
- **HTML in JS strings**: use template literals (backticks) for HTML containing variables — single quotes for HTML attributes
- **Avoid `!important`** in CSS whenever possible
- **NEVER use inline styles** — always use classes in `<style>` blocks or CSS files
- Use `==` not `===` for comparisons; no unnecessary `(int)` casts
- **EXCEPTION — strict comparison required for sentinel return values:**
  - `strpos()` returns `false` when not found and `0` when found at position 0 — always use `=== false` / `!== false` / `=== 0` / `!== 0`
- **Wrap `strpos()` in parentheses** when used in multi-condition `if` statements
- **Always add trailing commas** on the last item in arrays, objects, and parameter lists
- **When changing a function's return type**, always update its `@return` docblock to match
- Method names must start with a verb
- Use `let` not `var` in JavaScript
- Class naming: `App_Release_Manager_*` prefix for all classes
- Function naming: `rel_mng_*` prefix for standalone functions
- Constants: `APP_*` prefix

### Security
- **Always escape output** — use `htmlentities()` for HTML context
- **Sanitize input** — `strip_tags()`, `str_replace('..', '')` for path traversal prevention
- **Shell escaping** — always use `escapeshellarg()` for CLI parameters

### Performance
- **Cheapest checks first** in conditionals/loops
- **Don't process what you don't need** — skip early with `continue`/`unset`
- **Cache expensive operations** — e.g., WP version cached to file with 4h TTL

## Key Patterns & Conventions

### Plugin Validation Checklist (index.php)
Before allowing a release, the tool checks:
1. Stable tag matches plugin version
2. Has `Requires PHP` header
3. WC headers present (for WooCommerce plugins)
4. Tested with latest WP version
5. Changelog entry exists for current version
6. No uncommitted SVN changes
7. Not already released at this version

### Release Flow - Free Plugin (SVN)
1. `svn cp trunk/ tags/{version}/` on WordPress.org
2. Version recorded in `zzz_release.txt`

### Release Flow - Pro Plugin (Git)
1. Read `.gitignore`, `.release_manager_ignore`, `.distignore` for exclusions
2. Zip the plugin dir with exclusions via `zip` CLI
3. Generate `update.json` metadata
4. Extract changelog to `changelog.txt`
5. Git add, commit, pull, push release artifacts

### Archive Exclusions (file.php)
Default exclusions in `App_Release_Manager_File::archive()`: `.git*`, `.svn*`, `.log*`, `.bak*`, `.zip*`, `screenshot*`, `.gitignore`, `.release_manager_ignore`, `.distignore`, `nbproject`, `project`, `.claude/*`, `.vscode/*`, `.idea/*`, `.ht_sandbox_data/*`, `mu-plugins/*`, `doc/*`, `docs/*`, `zzz_*/*`, `zzz_project/*`

### Binary Detection
`App_Release_Manager_File::findBinary()` — finds custom binaries (`ogit`, `ozip`) with fallback:
1. `which` first (respects user PATH, finds non-global installs)
2. Check `/usr/local/bin/` and `/usr/bin/` as fallback
3. Return default fallback if not found
