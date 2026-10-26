# robots.txt Understanding (Bonus)

This bonus uses the standalone PHP demonstration in `demo/public/`. It is separate from the original Darkly findings described below. The demo shows that a `robots.txt` rule does not protect a file on the server.

## 1. Discovery

The original Darkly site exposes paths in `/robots.txt`. The file lists `/whatever` and `/.hidden`. In the current forwarded-port setup, each path without a trailing slash returns HTTP 301 to a trailing-slash URL. Each trailing-slash URL returns HTTP 200. The redirect's `Location` header drops port `8080`, so `curl -L` from this forwarded address tries port 80 and cannot follow it. On a direct VM address that serves HTTP on port 80, the redirect can be followed normally.

The independent PHP demo has its own `robots.txt`. It lists `/private/`. The demo also contains a static file at `/private/secret.txt` and a PHP route at `/protected.php` that checks a session authorization value.

## 2. Exploitation

Request `robots.txt` and then request the listed or known paths directly. A `Disallow` line tells cooperating web crawlers which paths the site owner asks them not to crawl. It does not prevent a browser or `curl` from requesting those paths.

In the original Darkly challenge, the observed results were:

```text
/robots.txt        HTTP 200; lists /whatever and /.hidden
/whatever          HTTP 301 to http://127.0.0.1/whatever/
/whatever/         HTTP 200
/.hidden           HTTP 301 to http://127.0.0.1/.hidden/
/.hidden/          HTTP 200
```

Use trailing slashes to reproduce the final 200 responses through local port forwarding:

```bash
curl -sS -i "http://127.0.0.1:8080/robots.txt"
curl -sS -D - -o /dev/null "http://127.0.0.1:8080/whatever"
curl -sS -o /dev/null -w '/whatever/ HTTP %{http_code}\n' "http://127.0.0.1:8080/whatever/"
curl -sS -D - -o /dev/null "http://127.0.0.1:8080/.hidden"
curl -sS -o /dev/null -w '/.hidden/ HTTP %{http_code}\n' "http://127.0.0.1:8080/.hidden/"
```

In the independent PHP demo, the observed results were:

```text
/robots.txt                  HTTP 200
/private/secret.txt          HTTP 200
/protected.php               HTTP 403 without authorization
```

## 3. Technical Explanation

`robots.txt` is a public file for crawler guidance. It is not an access control system. A crawler may follow its rules, but any client can ignore them and request a listed path. A `Disallow` entry can also reveal a path that the site owner may prefer to keep less visible.

The PHP demo shows two different server behaviors. The static file `/private/secret.txt` is inside the public web root, so the web server serves it directly with HTTP 200. The `protected.php` script checks `$_SESSION['authorized']` and returns HTTP 403 when that value is empty. This script does not protect the separate static file. Its 403 response does not make the static file private.

Directory listing is a separate server setting. If directory indexing is enabled, a request to a directory without an index file may show the names of files in that directory. If indexing is disabled, the server may return a 403 or another response. `robots.txt` does not control directory listing.

## 4. Security Impact

An exposed path can help an attacker find private files, backups, configuration files, or test data. A publicly readable file can disclose its contents even when `robots.txt` says not to crawl it. Directory listing can reveal additional file names and make discovery easier.

The original Darkly findings confirm that the trailing-slash paths `/whatever/` and `/.hidden/` were accessible with HTTP 200 after the bare paths redirected. The PHP demo confirms that its static secret file is accessible and that its separate protected PHP route rejects an unauthorized request. These results do not prove that other files or systems are exposed.

## 5. Prevention

Use server-side access control for every sensitive route and file. Do not rely on `robots.txt`, hidden names, or directory names to protect data. Disable directory indexing unless the application needs it.

Keep sensitive files outside the public web root. For example:

```text
app/
├── public/
│   └── protected.php
└── private/
    └── secret.txt
```

The web server can serve files from `public/`, but it cannot serve `private/secret.txt` as a direct URL. The application should check the user's authorization first, then read the private file from a fixed server-side path and return it only when access is allowed. Do not accept a client supplied file path for this operation.

## 6. Live Demonstration

Start the PHP server from the repository root. The document root is the demo's `public/` directory:

```bash
php -S 127.0.0.1:8002 -t bonus/robots/Resources/demo/public
```

Keep the server running. In a second terminal, request the demo `robots.txt`:

```bash
curl -sS -i "http://127.0.0.1:8002/robots.txt"
```

Expected result: HTTP 200, with this body:

```text
User-agent: *
Disallow: /private/
```

Request the static file directly:

```bash
curl -sS -i "http://127.0.0.1:8002/private/secret.txt"
```

Expected result: HTTP 200 and the body `INTERNAL TEST DATA`. This demonstrates that the `robots.txt` rule does not protect the file.

Request the PHP route without an authorized session:

```bash
curl -sS -i "http://127.0.0.1:8002/protected.php"
```

Expected result: HTTP 403 and the body `Access denied`.

You can also inspect the directory URL:

```bash
curl -sS -i "http://127.0.0.1:8002/private/"
```

In the tested PHP 8.5.11 development server, `/private/` returned HTTP 404. Directory listing behavior depends on the web server configuration. If it is enabled, the response may show file names. The other three tested outcomes above are HTTP 200, HTTP 200, and HTTP 403. Stop the server with `Ctrl-C` when the demonstration is complete. PHP must be installed, and you can change port `8002` if it is in use.

The original Darkly paths are separate from the demo on port `8002`. The demo server does not reproduce the Darkly application responses.
