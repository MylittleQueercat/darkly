# Bruteforce Understanding (Bonus)

This bonus uses the standalone PHP login demo in `demo/login.php`. It explains brute-force risks and shows a session-based rate limit. It is separate from the original Darkly Bruteforce (member) challenge documented in `mandatory/bruteforce/Resources/README.md`.

The original challenge uses a GET login form at `?page=signin`. The verified credentials are `admin / shadow`, and they return the challenge flag. The original challenge did not test rate limiting. The rate-limit behavior below belongs to this independent PHP demo.

## 1. Discovery

The PHP demo reads a `mode` and a `password` from the query string. It supports `unsafe` and `safe` modes. In `safe` mode, it stores failed-attempt times in a PHP session. The limit is three failed attempts in a 60-second window.

The demonstration uses one client session for each five-attempt sequence. This is important because the safe mode counts attempts by the PHP session cookie.

## 2. Exploitation

A brute-force attack tests many password values against one account. A dictionary attack tests a list of likely passwords. A credential-stuffing attack tests username and password pairs that were exposed in other data breaches. It does not guess new passwords; it relies on password reuse.

Weak and common passwords make these attacks more likely to succeed. A password list is only a set of candidates. A password that is not in the list will not be found by that dictionary run. In the original Darkly challenge, the verified password `shadow` was confirmed separately. It was not found by the existing candidate list.

## 3. Technical Explanation

In `unsafe` mode, the demo checks the password but does not record failed attempts. The tested sequence returns HTTP 401 for all five incorrect passwords.

In `safe` mode, the demo stores failed-attempt timestamps in `$_SESSION['attempts']`. It removes timestamps older than 60 seconds. After three failed attempts in the current session, it returns HTTP 429 for further attempts in that session. The tested sequence returns HTTP 401 for attempts one to three and HTTP 429 for attempts four and five.

The demo accepts `shadow` as its password. A valid password in safe mode clears the stored failed-attempt list, if the request reaches the password check.

## 4. Security Impact

Without controls, an attacker can test many candidates. A dictionary attack may find a common or reused password. Credential stuffing may give access to accounts where users reused a password exposed elsewhere. A successful login can expose that account's private data and functions.

The demo shows a per-session limit. It does not show account-level protection. An attacker may start new sessions or use multiple clients. A session-only limit can therefore be bypassed by changing or rotating the session. This demonstration does not test a production service or measure its response to distributed attempts.

## 5. Prevention

Apply limits across sessions and clients. Track failures for the account, and combine this with source-based signals and monitoring. Use progressive delays or temporary challenges. Avoid permanent account lockout as the only control, because an attacker could use it to deny service to a user.

Require long, unique passwords. Store passwords with a modern password hashing function designed for passwords, with a unique salt. Use multi-factor authentication (MFA), especially for sensitive accounts. Detect unusual login patterns and alert on repeated failures. Use generic failure messages so the response does not reveal which part of a login was correct.

The demo stores counters in a PHP session. Session-based controls can help limit repeated requests from one session, but they are not sufficient alone. A client can discard a cookie and obtain a new session. Use shared, server-side account-level controls that remain effective across sessions and clients.

## 6. Live Demonstration

Start the PHP server from the repository root:

```bash
php -S 127.0.0.1:8001 -t bonus/bruteforce/Resources/demo
```

Keep the server running. In a second terminal, run five incorrect attempts in unsafe mode:

```bash
for attempt in 1 2 3 4 5; do
  curl -sS -o /dev/null -w "attempt=$attempt HTTP %{http_code}\n" -G \
    --data-urlencode 'mode=unsafe' \
    --data-urlencode "password=wrong-$attempt" \
    "http://127.0.0.1:8001/login.php"
done
```

Expected output:

```text
attempt=1 HTTP 401
attempt=2 HTTP 401
attempt=3 HTTP 401
attempt=4 HTTP 401
attempt=5 HTTP 401
```

Now run five incorrect attempts in safe mode. The temporary cookie jar keeps the same PHP session for all five requests. A new temporary file gives this sequence a fresh session:

```bash
cookie_jar=$(mktemp)
trap 'rm -f "$cookie_jar"' EXIT
for attempt in 1 2 3 4 5; do
  curl -sS -o /dev/null -w "attempt=$attempt HTTP %{http_code}\n" \
    -b "$cookie_jar" -c "$cookie_jar" -G \
    --data-urlencode 'mode=safe' \
    --data-urlencode "password=wrong-$attempt" \
    "http://127.0.0.1:8001/login.php"
done
```

Expected output:

```text
attempt=1 HTTP 401
attempt=2 HTTP 401
attempt=3 HTTP 401
attempt=4 HTTP 429
attempt=5 HTTP 429
```

The unsafe sequence has no attempt limit. The safe sequence limits one PHP session after three failed attempts. This is a demonstration of session-based rate limiting, not proof of account-level protection. Stop the PHP server with `Ctrl-C` when the demonstration is complete. The local address is `127.0.0.1:8001`; change it if the port is in use. The demo requires PHP CLI 7.4 or later because it uses arrow functions, and it requires `curl` for the requests.
