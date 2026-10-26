# Survey

## 1. Discovery

The survey page is available at `/?page=survey`. Its HTML form offers scores from 1 to 10. The form sends a `POST` request with the fields `sujet` and `valeur`. These fields come from the client, so inspect whether the server enforces the score range.

## 2. Exploitation

Send a score outside the range shown by the form:

```bash
curl -s -X POST \
-d 'sujet=2&valeur=1000' \
"http://127.0.0.1:8080/?page=survey" \
| grep "Good Job"
```

Observed result: the response displays the challenge flag.

## 3. Technical Explanation

The form presents values from 1 to 10, but a client can change the submitted `valeur` field. The observed request with `sujet=2` and `valeur=1000` returns the flag. This indicates that the server does not enforce the form's score range for this request. The request result alone does not show the server's exact validation or storage logic.

## 4. Security Impact

An attacker can submit a score that the form does not offer. If the server accepts such values, survey results can be inaccurate or manipulated. Other server actions that rely on the submitted value may also be affected.

## 5. Prevention

Validate every submitted field on the server. Check that `sujet` is an allowed survey item and that `valeur` is an integer within the permitted range of 1 to 10. Reject invalid values before processing or storing them. Client-side form limits can help users, but they cannot replace server-side validation.

## 6. Flag Verification

Run the command from the repository root. It submits the observed values, matches the response's `Good Job` flag text, and compares the extracted value with `mandatory/survey/flag`:

```bash
curl -s -X POST \
-d 'sujet=2&valeur=1000' \
"http://127.0.0.1:8080/?page=survey" \
| grep -oE 'Good Job[^<]*[a-f0-9]{64}' \
| grep -oE '[a-f0-9]{64}' \
| diff - mandatory/survey/flag
```

An exit status of `0` means that the retrieved flag matches the saved flag. The address `127.0.0.1:8080` is our local VirtualBox port-forwarding address. Adjust it if the evaluation environment uses a different address or port.
