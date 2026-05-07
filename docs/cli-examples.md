# CLI examples (Penneo SDK for PHP)

Runnable PHP scripts in this folder:

| Script | Purpose |
|--------|---------|
| [casefile-e2e-demo.php](casefile-e2e-demo.php) | Full flow: folder, case file, documents, signer, signing request, copy recipient, activate (see below). |
| [programmatic_oauth_example.php](programmatic_oauth_example.php) | OAuth without browser (server-side / no redirect). |
| [interactive_oauth_example.php](interactive_oauth_example.php) | OAuth with PKCE redirect flow (`?code=` callback). |

The root [README](../README.md) links to the OAuth examples from the authentication section.

### How to try the OAuth scripts

From the repository root (after `composer install`), replace the placeholder `clientId` / `clientSecret` / `apiKey` / `apiSecret` (and for interactive, `redirectUri` registered at Penneo), then:

```bash
# Programmatic — needs valid OAuth client + API key/secret; reaches API on CaseFile::persist()
php docs/programmatic_oauth_example.php
```

```bash
# Interactive — from repo or from docs/, `php interactive_oauth_example.php` prints the Penneo authorize URL
# (header() alone shows nothing in CLI). To complete the flow use a browser:
php -S 127.0.0.1:8080 -t docs
# Open http://127.0.0.1:8080/interactive_oauth_example.php — login — callback with ?code= runs persist()
```

Without real credentials, programmatic fails at token exchange (e.g. HTTP 401) and interactive still performs the redirect to Penneo’s authorize URL (verify in browser).

### Interactive OAuth on localhost (step by step)

Penneo sends the user back to a URL you register on the OAuth client. **That URL, the address bar in the browser, and `PENNEO_OAUTH_REDIRECT_URI` must be identical** (including `http` vs `https`, `localhost` vs `127.0.0.1`, port, path, and **`/interactive_oauth_example.php`** — the file extension is required).

1. In Penneo (sandbox), add a redirect URI such as:  
   `http://127.0.0.1:8080/interactive_oauth_example.php`

2. From the **repository root** (where `vendor/` lives):

   ```bash
   composer install
   export PENNEO_OAUTH_CLIENT_ID="your_client_id"
   export PENNEO_OAUTH_CLIENT_SECRET="your_client_secret"
   # Optional if you use localhost instead of 127.0.0.1 everywhere:
   # export PENNEO_OAUTH_REDIRECT_URI="http://localhost:8080/interactive_oauth_example.php"

   php -S 127.0.0.1:8080 -t docs
   ```

3. Open **exactly**:  
   `http://127.0.0.1:8080/interactive_oauth_example.php`  
   (not `…/interactive_oauth_example` without `.php` unless your server maps it)

4. Log in at Penneo; after redirect you should see plain text like `OK — Case file created. id=…`

**Typical failures**

| What you see | Cause |
|--------------|--------|
| `redirect_uri_mismatch` | Redirect URI in Penneo ≠ URL in browser or ≠ env var. |
| `Missing PKCE code_verifier` | Started flow in one browser/session, callback in another; use one tab, avoid clearing cookies mid-flow. |
| Blank or 404 | Wrong path: add `.php`; ensure `-t docs` and script is under `docs/`. |
| Prompt for env vars | Export `PENNEO_OAUTH_CLIENT_ID` / `PENNEO_OAUTH_CLIENT_SECRET` in the **same terminal** where you run `php -S` (child inherits env). |

## `casefile-e2e-demo.php`

Demo script: creates a **folder**, a **case file** with **annex document + signable document**, **signer**, **signature line**, **signing request**, **copy recipient**, then **activates** the case file (no `send()` — no automated outbound e-mail). It prints the **signing link** at the end.

### Prerequisites

From the repository root:

```bash
composer install
```

### Minimal run (WSSE, default sandbox)

The SDK defaults to `https://sandbox.penneo.com/api/v1/` unless you set a different `PENNEO_API_BASE`.

```bash
export PENNEO_WSSE_KEY="your_key"
export PENNEO_WSSE_SECRET="your_secret"

php docs/casefile-e2e-demo.php
```

Optional (reseller account, acting on behalf of a customer):

```bash
export PENNEO_WSSE_USER="12345"   # Penneo customer id
```

### Programmatic OAuth (API v3)

Same kind of setup as in the main README (`client_id`, `client_secret`, API key + API secret).

```bash
export PENNEO_AUTH=oauth
export PENNEO_OAUTH_ENV=sandbox    # or production
export PENNEO_CLIENT_ID="..."
export PENNEO_CLIENT_SECRET="..."
export PENNEO_API_KEY="..."
export PENNEO_API_SECRET="..."

php docs/casefile-e2e-demo.php
```

### Optional environment variables (demo)

| Variable | Purpose |
|----------|---------|
| `PENNEO_DEMO_PDF` | Path to your PDF; if unset, a minimal temporary PDF is generated. |
| `PENNEO_DEMO_SIGNER_EMAIL` | E-mail on the `SigningRequest` (default: demo placeholder). |
| `PENNEO_DEMO_COPY_EMAIL` | E-mail for the **CopyRecipient** (default: demo placeholder). |
| `PENNEO_API_BASE` | API base URL for WSSE (see Production below). |

### Sandbox → production

**WSSE:** use **production** credentials and the live API base, for example:

```bash
export PENNEO_API_BASE="https://app.penneo.com/api/v1/"
export PENNEO_WSSE_KEY="..."
export PENNEO_WSSE_SECRET="..."
```

Confirm the exact API path (`v1` or other) with Penneo for your account.

**OAuth:** set `PENNEO_OAUTH_ENV=production` and production credentials (client + keys). The SDK will use the production signing API host (`https://app.penneo.com` / `api/v3/` as configured).

Do not reuse sandbox keys or secrets in production.

### Troubleshooting

For clearer HTTP errors you can use `ApiConnector::throwExceptions(true)` in your code (the demo script already does). For production, `ApiConnector::setLogger(...)` helps capture request ids for support.
