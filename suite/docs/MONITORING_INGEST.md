# Cross-project log ingest (`/ingest/logs`)

**Xquisite Creations Suite is the canonical monitoring/log hub for every one
of this user's projects.** Reporter apps (Keystone/Loan, bx-eventos,
jblack-mc/xquisite, and any future project) push their `error`-and-above
events here; they land in `system_logs` and show in the existing central log
viewer at **`/admin/logs?source=<slug>`**. Uptime/health for the same apps is
the separate `/admin/monitoring` dashboard.

No other project should stand up its own copy of this hub (MonitoredInstance
registry + system_logs table + ingest endpoint) — that just splits the data
across two dashboards. Every other repo is a **reporter**: a few env vars and
one small scheduled job pointed at this hub.

## Registering a reporter

### Fastest: the artisan command (on this hub)

```
php artisan monitoring:register-instance <slug> --name="<Label>" --url=https://<reporter>/api/health
```

Creates the `MonitoredInstance` with a fresh `Str::random(48)` token, `is_active=true`,
and prints the four `MONITORING_*` env lines to paste into the reporter. Idempotent —
re-running with the same slug only fills gaps (keeps the token unless `--rotate-token`).
Passing `--name` also adopts a pre-slug legacy row of that exact name instead of
creating a duplicate. `--deactivate` flips `is_active` off.

### Or the UI

`/admin/monitoring` → **Add Instance** (permission: `manage-tenants`, i.e.
super-admin):

| Field | Value |
|---|---|
| `name` | Human label, e.g. `Keystone` |
| `slug` | Stable id, e.g. `keystone`. Written to `system_logs.source` for every forwarded row and used as the log-viewer filter. Lowercase, `alpha_dash`. **Required** for `/ingest/logs` to accept anything. |
| `url` | The reporter's own pull-health endpoint, e.g. `https://keystone.example/api/health` |
| `api_token` | `Str::random(48)` — hand this to the reporter as `MONITORING_TOKEN` |
| `is_active` | must be true; flipping it false makes every endpoint 401 for that reporter |

## Endpoints

| Route | File | Purpose |
|---|---|---|
| `POST /ingest/logs` | `routes/web.php` | batch error events → `system_logs` |
| `OPTIONS /ingest/logs` | `routes/web.php` | CORS preflight |
| `POST /api/health-report` | `routes/api.php` | heartbeat → `health_check_logs` + instance status |
| `GET /api/health-status` | `routes/api.php` | reporter polls its own status |

All four are CSRF-exempt (`/ingest/logs` explicitly in `bootstrap/app.php`; the
two `/api/*` routes sit in the stateless `api` middleware group, which never
applies CSRF) and gated by the `monitored-instance` middleware
(`App\Http\Middleware\EnsureMonitoredInstance`) — bearer token → active
`MonitoredInstance`, stashed on the request as `monitored_instance`.

## `POST /ingest/logs` contract

```
Authorization: Bearer <api_token>
Content-Type: application/json
Accept: application/json
```

```jsonc
{
  "events": [                     // 1..100 per request
    {
      "level": "error",           // required; case-insensitive; normalised to
                                  //   UPPERCASE; unknown -> ERROR
      "message": "RuntimeException: ...",  // required; truncated to 2000 chars
      "logged_at": "2026-09-03T10:15:03Z", // required; ISO-8601; stored in
                                  //   context, NOT used for row ordering
      "context": { "file": "...", "line": 42, "trace": ["..."] }, // optional;
                                  //   encoded JSON truncated to ~16 KB (trace
                                  //   dropped first)
      "file": "app/...", "line": 42, "url": "https://...",        // optional
      "request_id": "01J...",     // optional
      "fingerprint": "keystone-1234"  // optional but recommended =
                                  //   "{slug}-{reporter row id}"; drives dedup
    }
  ]
}
```

### Responses

| Status | Body | Meaning |
|---|---|---|
| `200` | `{"accepted":N,"duplicates":M,"instance":"<slug>"}` | batch stored (one transaction) |
| `401` | `{"error":"Unauthorized",...}` / `{"error":"Invalid token",...}` | no / bad / inactive token |
| `422` | validation bag | bad envelope, or the instance has no `slug` |
| `429` | throttle + `Retry-After` | >120 req/min for this token (`ingest` limiter, keyed by token) |

The reporter should treat any non-2xx as "retry next run" and not advance its
watermark.

## Behaviour on ingest

- Each event becomes a `system_logs` row: `source = <slug>`, `status = 'new'`,
  `level` uppercased, `user_id = null` (reporter user ids are meaningless here),
  `created_at` = **hub receive time** (a skewed reporter clock must not pin rows
  to the top of the viewer), `dedup_key = sha1(instance_id | fingerprint)`.
- **Dedup:** rows whose `dedup_key` already exists for that `source` are skipped
  and counted in `duplicates`. Enforced in-app, not by a unique constraint
  (`system_logs` is a large shared table).
- **Alert:** if the batch contains an `ERROR`+ row and no alert has fired for
  this source in the last 15 min (`Cache` cooldown), one `CriticalLogAlert`
  mail goes to `config('mail.from.address')` — mirrors `App\Logging\DatabaseLogger`.
  `CriticalLogAlert` is `ShouldQueue`; if this hub's `QUEUE_CONNECTION` is not
  `sync`, make sure a worker runs or those mails will sit in the queue.
- Forwarded `ERROR`+ rows count toward the sidebar's unresolved-critical badge
  (`SystemLog::unresolvedCriticalCount()`), same as this app's own errors.

## Reporter-side setup (every other project)

Four env vars, pointed at this hub — nothing else changes per project:

```
MONITORING_ENABLED=true
MONITORING_URL=https://<this hub's base URL>
MONITORING_TOKEN=<api_token from /admin/monitoring>
MONITORING_SLUG=<slug from /admin/monitoring>
```

Then one scheduled job that batches whatever the reporter already logs
(its own `system_logs`-equivalent, or straight from its log channel) and
`POST`s it to `{MONITORING_URL}/ingest/logs` with the contract above — plus,
separately, a heartbeat job hitting `{MONITORING_URL}/api/health-report`. Keep
batches ≤100 events and under the 120/min token-keyed rate limit.

## Deploy notes

`php artisan migrate` adds `monitored_instances.slug` and
`system_logs.dedup_key`. Backfill `slug` on any pre-existing instance rows
before pointing reporters at the hub, or their logs get `source = NULL` and
vanish from the filter.

`php artisan route:list | grep -E 'ingest|health-report|health-status'` should
show `ingest.logs` under `web` and `health.report`/`health.status` under `api`.
`route:cache` still works — no closure routes were added.

## Pull vs push health

`instances:check-health` (scheduler, every 5 min, `routes/console.php`) also
*pulls* `MonitoredInstance.url` (expects `GET {url}/api/health`, bearer-token
authenticated). That and the pushed heartbeat (`/api/health-report`) can
disagree (a slow box answers the pull late but pushes "up" fine). Per
instance: point `url` at the real `/api/health` and treat the push as
authority, or blank it / deactivate the pull.

## Known pre-existing quirks (not caused by ingest)

- `admin/monitoring/show.blade.php` reads `$lastCheck->version` /
  `->db_connection` / `->queue_status` as columns; they actually live inside
  the `metadata` JSON, so those cells render blank. Worth a follow-up fix if
  that page is used to actually diagnose a down instance.
- The `monitored_instances.api_token` migration calls `->encrypted()`, which
  is not a real Blueprint modifier and silently does nothing — the column is
  plain-text in the DB, matching what every controller's `where('api_token', $token)`
  lookup actually requires (Eloquent's real `encrypted` cast, applied via
  `$casts` on the model, would make direct equality lookups like this
  impossible — ciphertext is non-deterministic per encryption). Not a live bug,
  but the migration line reads as more secure than it is; a genuine hardening
  pass would hash tokens (SHA-256, like Sanctum) rather than rely on the
  no-op call.
