# CLI examples (Penneo SDK PHP)

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

php examples/casefile-e2e-demo.php
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

php examples/casefile-e2e-demo.php
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
