# Redirect Understanding (Bonus)

This bonus explains open redirects with two standalone PHP examples in `demo/`. It is an independent security demonstration. It is separate from the original Darkly Redirect challenge documented in `mandatory/redirect/Resources/README.md`.

## 1. Discovery

The `unsafe.php` demo reads the `url` query parameter and places its value in the HTTP `Location` header. The `safe.php` demo reads a `site` name and checks it against a fixed list of approved destinations.

The original Darkly challenge has different observed behavior. Its `site=facebook` value redirects, while an unexpected value displays the challenge flag. That result did not demonstrate an arbitrary external redirect. The arbitrary redirect described here was tested with the independent `unsafe.php` demo.

## 2. Exploitation

Start the PHP development server from the repository root:

```bash
php -S 127.0.0.1:8000 -t bonus/redirect/Resources/demo
```

Keep this server running. In a second terminal, send an external destination to the unsafe demo:

```bash
curl -sS -D - -o /dev/null -G \
--data-urlencode 'url=https://attacker.example/login' \
"http://127.0.0.1:8000/unsafe.php"
```

Observed result: HTTP 302, with a `Location` header that points to `https://attacker.example/login`.

Send an approved destination to the safe demo:

```bash
curl -sS -D - -o /dev/null -G \
--data-urlencode 'site=facebook' \
"http://127.0.0.1:8000/safe.php"
```

Observed result: HTTP 302, with a `Location` header that points to the approved Facebook destination.

Now send an unknown destination to the safe demo:

```bash
curl -sS -i -G \
--data-urlencode 'site=https://attacker.example/login' \
"http://127.0.0.1:8000/safe.php"
```

Observed result: HTTP 400 and the response body says `Invalid redirect target`.

## 3. Technical Explanation

An HTTP redirect uses a 3xx response and a `Location` header. In `unsafe.php`, the code reads `$_GET["url"]` and appends that client supplied value to the header without an allowlist. The tested request shows that an external URL can control the redirect destination.

In `safe.php`, the code accepts a short key such as `facebook`. It looks up that key in a server defined array. If the key is not present, it returns HTTP 400. If the key is approved, it uses the fixed destination in the array. This limits the destination to values defined by the application.

These explanations describe the two demo scripts. They do not change or extend what was observed in the original Darkly challenge.

## 4. Security Impact

An attacker can use an open redirect on a trusted website to send a victim to an attacker controlled site. The trusted domain in the link can make the destination appear safe. An attacker may use this for phishing or to make harmful links harder to identify.

Open redirects can also weaken security flows that send users to a return URL after login or another action. If the application accepts an unsafe return URL, it may send a user or sensitive data to an unintended destination. The demo proves the redirect behavior. It does not test a login flow, token leak, or any other impact beyond the redirect.

## 5. Prevention

Use a strict allowlist of destination keys. Map each key to a fixed URL on the server, as `safe.php` does. Reject unknown keys. Do not put a client supplied URL directly in a `Location` header.

If the application must accept a destination URL, parse and validate it on the server. Allow only approved schemes and exact approved hosts. Reject user information, unexpected ports, ambiguous encodings, and destinations outside the approved set. A string prefix check is not sufficient for host validation.

## 6. Live Demonstration

Run the PHP server command in the second section from the repository root. Use a second terminal for the three `curl` requests. The requests show the unsafe external redirect, the safe approved redirect, and the safe rejection of an unknown destination. Check the status line and `Location` header for each response. Stop the PHP server with `Ctrl-C` when the demonstration is complete.

The server uses `127.0.0.1:8000` for this local demonstration. Change the address or port if they are already in use. The PHP command requires PHP to be installed.
