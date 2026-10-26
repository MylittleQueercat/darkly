# 42 Darkly — Web Security Evaluation Notes

This repository contains reproduction notes for the 42 Darkly web security project. It records the assigned vulnerability findings, verified flags, and three independent bonus demonstrations. Use the Darkly virtual machine supplied for the project. The vulnerable application is a local training target.

The six numbered README sections used in this repository are a team documentation convention. They are not presented as official subject headings.

## Project scope

### Mandatory findings assigned to hguo

| Vulnerability | Documentation |
| --- | --- |
| Bruteforce (member) | [README](mandatory/bruteforce/Resources/README.md) |
| Cookies | [README](mandatory/cookies/Resources/README.md) |
| File Upload | [README](mandatory/file_upload/Resources/README.md) |
| Include | [README](mandatory/include/Resources/README.md) |
| Redirect | [README](mandatory/redirect/Resources/README.md) |
| Spoof (curl) | [README](mandatory/spoof/Resources/README.md) |
| Survey | [README](mandatory/survey/Resources/README.md) |

The seven flags are stored beside each vulnerability's `Resources/` directory. The Bruteforce candidate list is in `mandatory/bruteforce/Resources/passwords.txt`.

### Bonus demonstrations

| Topic | Documentation | Demo |
| --- | --- | --- |
| Redirect Understanding | [README](bonus/redirect/Resources/README.md) | `bonus/redirect/Resources/demo/` |
| Bruteforce Understanding | [README](bonus/bruteforce/Resources/README.md) | `bonus/bruteforce/Resources/demo/` |
| robots.txt Understanding | [README](bonus/robots/Resources/README.md) | `bonus/robots/Resources/demo/public/` |

The bonus PHP examples are independent demonstrations. They are not the original Darkly application findings.

## Team responsibilities

The recorded assignment for **hguo** is the seven mandatory vulnerabilities listed above. The repository contains three bonus demonstrations. It does not record individual ownership for other team members, so this guide does not assign them work.

## Environment setup

1. Obtain and start the Darkly virtual machine using the project instructions. Do not add the ISO image to this repository.
2. Find the VM address and configure the local port forwarding required by your setup.
3. Confirm that the Darkly site responds. The mandatory command examples use `http://127.0.0.1:8080`; replace that host and port when your environment uses another address.
4. Install `curl`, `grep`, and `diff` for the mandatory reproduction and flag checks.
5. Install PHP CLI 7.4 or later and `curl` to run the bonus demos. The Bruteforce PHP demo uses arrow-function syntax, which requires PHP 7.4 or later.

Check the available tools with:

```bash
curl --version
php -v
```

The bonus README files provide the PHP server commands and the expected HTTP responses.

## Evaluation instructions

Run commands from the repository root unless a README states otherwise. Read each mandatory README, follow the discovery and exploitation steps, and run its flag verification command. An exit status of `0` from the `diff` command means that the retrieved flag matches the saved flag.

For a bonus demonstration, start its PHP server in one terminal and run the `curl` commands from a second terminal. Stop the server with `Ctrl-C` when the demonstration is complete. The bonus README files state the expected status codes and describe the limits of each demo.

Use the evaluation instructions supplied by your campus for the exact scoring rules. Keep the ISO and other binary files out of the submission. Before evaluation, confirm that all required files are included in the repository being submitted.
